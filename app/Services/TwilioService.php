<?php

namespace App\Services;

use App\Models\CallSession;
use Illuminate\Support\Facades\Log;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class TwilioService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
    }

    public function initiateCall(CallSession $callSession, string $toPhone, string $vaPhone): string
    {
        $twimlUrl = route('api.twilio.twiml', ['callSession' => $callSession->id]);

        $call = $this->client->calls->create(
            $toPhone,
            config('services.twilio.phone_number'),
            [
                'url' => $twimlUrl,
                'statusCallback' => route('api.twilio.status'),
                'statusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
                'statusCallbackMethod' => 'POST',
            ]
        );

        return $call->sid;
    }

    public function generateTwiML(CallSession $callSession): string
    {
        $response = new VoiceResponse;

        // CRITICAL: DO NOT refresh the contact relationship here!
        // The TwilioWebhookController already sets the correct contact with setRelation()
        // Refreshing would reload the wrong contact from the database
        // Only refresh if contact is not already loaded
        if (! $callSession->relationLoaded('contact')) {
            $callSession = $callSession->fresh(['contact']);
        }

        if (! $callSession->contact) {
            Log::error('Call session missing contact relationship', [
                'call_session_id' => $callSession->id,
                'contact_id' => $callSession->contact_id,
            ]);
            $response->say('Sorry, contact information is missing. Goodbye.', ['voice' => 'alice']);

            return $response->asXML();
        }

        // Log the contact details being used for TwiML generation
        $contactPhone = $callSession->contact ? ($callSession->contact->phone ?? null) : null;
        $contactIdFromRelation = $callSession->contact ? ($callSession->contact->id ?? null) : null;

        Log::info('Generating TwiML with contact details', [
            'call_session_id' => $callSession->id,
            'call_session_contact_id' => $callSession->contact_id,
            'contact_id_from_relation' => $contactIdFromRelation,
            'contact_phone' => $contactPhone ?? 'N/A',
            'contact_name' => $callSession->contact->full_name ?? 'N/A',
            'contact_loaded_from' => $callSession->relationLoaded('contact') ? 'relation' : 'database',
            'contact_exists' => $callSession->contact !== null,
        ]);

        // Clean phone number - remove any Unicode formatting characters and ensure proper format
        if (! $callSession->contact || ! $contactPhone) {
            Log::error('Contact phone number is missing in generateTwiML', [
                'call_session_id' => $callSession->id,
                'contact_exists' => $callSession->contact !== null,
                'contact_phone' => $contactPhone,
            ]);
            $response->say('Sorry, contact information is missing. Goodbye.', ['voice' => 'alice']);

            return $response->asXML();
        }

        $phoneNumber = $contactPhone;
        // Remove all Unicode directional formatting characters
        $phoneNumber = preg_replace('/[\x{200E}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $phoneNumber);
        // Remove any non-digit characters except + at the start
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        $phoneNumber = trim($phoneNumber);

        if (empty($phoneNumber)) {
            Log::error('Invalid phone number for call session', [
                'call_session_id' => $callSession->id,
                'original_phone' => $callSession->contact->phone,
            ]);

            $response->say('Sorry, the phone number is invalid.', ['voice' => 'alice']);

            return $response->asXML();
        }

        // Only add Stream if media stream URL is configured
        $mediaStreamUrl = config('app.media_stream_url', env('MEDIA_STREAM_URL'));

        // For browser-based calling with Media Streams:
        // Use <Start><Stream> BEFORE <Dial> to initiate the media stream
        // <Start> initiates the stream without redirecting the call, allowing <Dial> to execute
        // This is the correct TwiML structure for Media Streams with Dial
        if ($mediaStreamUrl) {
            // Append parameters directly to the URL as query string
            $urlParams = http_build_query([
                'callSessionId' => (string) $callSession->id,
            ]);

            if ($callSession->twilio_call_sid) {
                $urlParams .= '&'.http_build_query(['twilioCallSid' => $callSession->twilio_call_sid]);
            }

            // Append query string to URL
            $streamUrl = $mediaStreamUrl.(str_contains($mediaStreamUrl, '?') ? '&' : '?').$urlParams;

            // Use Start verb to initiate the stream (doesn't redirect the call)
            $start = $response->start();
            $stream = $start->stream([
                'url' => $streamUrl,
                'track' => 'both_tracks', // Receive both inbound (agent) and outbound (prospect) audio tracks
            ]);

            // Add Parameter elements to Stream
            $stream->parameter(['name' => 'callSessionId', 'value' => (string) $callSession->id]);
            if ($callSession->twilio_call_sid) {
                $stream->parameter(['name' => 'twilioCallSid', 'value' => $callSession->twilio_call_sid]);
            }
        }

        // Log the phone number that will be dialed
        Log::info('Dialing phone number in TwiML', [
            'call_session_id' => $callSession->id,
            'contact_id' => $callSession->contact_id,
            'phone_number_to_dial' => $phoneNumber,
            'original_contact_phone' => $callSession->contact->phone ?? 'N/A',
        ]);

        // Dial the phone number
        // This will execute after Start, allowing both streaming and dialing to work
        $dial = $response->dial(null, [
            'callerId' => config('services.twilio.phone_number'),
            'timeout' => 30, // Wait up to 30 seconds for the call to be answered
            'action' => route('api.twilio.status'), // Status callback URL
        ]);

        $dial->number($phoneNumber);

        return $response->asXML();
    }

    public function getCall(string $callSid)
    {
        return $this->client->calls($callSid)->fetch();
    }

    public function generateAccessToken(int $userId, ?string $identity = null): string
    {
        $identity = $identity ?? (string) $userId;
        $accountSid = config('services.twilio.account_sid');
        $apiKey = config('services.twilio.api_key');
        $apiSecret = config('services.twilio.api_secret');
        $applicationSid = config('services.twilio.application_sid');

        if (! $accountSid) {
            throw new \RuntimeException('Twilio Account SID is not configured. Please set TWILIO_ACCOUNT_SID in your .env file.');
        }

        if (! $apiKey || ! $apiSecret) {
            throw new \RuntimeException('Twilio API Key and Secret must be configured for browser-based calling. Please set TWILIO_API_KEY and TWILIO_API_SECRET in your .env file. You can create these in the Twilio Console under Account > API Keys & Tokens.');
        }

        $token = new AccessToken(
            $accountSid,
            $apiKey,
            $apiSecret,
            3600,
            $identity
        );

        $voiceGrant = new VoiceGrant;

        // Set outgoing application SID - REQUIRED for browser-based outgoing calls
        // The application must be configured with a TwiML URL in Twilio Console
        if ($applicationSid) {
            $voiceGrant->setOutgoingApplicationSid($applicationSid);
        } else {
            // Try to get or create application automatically
            $applicationSid = $this->getOrCreateApplication();
            if ($applicationSid) {
                $voiceGrant->setOutgoingApplicationSid($applicationSid);
            } else {
                throw new \RuntimeException(
                    'Twilio Application SID is required for browser-based calling. '.
                    'Please set TWILIO_APPLICATION_SID in your .env file. '.
                    'To create one: Go to Twilio Console > Phone Numbers > Manage > TwiML Apps > Create new TwiML App. '.
                    'Set the Voice Configuration URL to: '.route('api.twilio.twiml', ['callSession' => 'YOUR_SESSION_ID'])
                );
            }
        }

        $token->addGrant($voiceGrant);

        return $token->toJWT();
    }

    protected function getOrCreateApplication(): ?string
    {
        try {
            $appName = config('app.name', 'VA Call Assistant').' Browser Calling';

            // For browser-based calling, we should NOT set a Voice URL on the Application
            // The actual TwiML URL is passed in the device.connect() call's 'To' parameter
            // Setting a Voice URL on the Application causes Twilio to ignore the 'To' parameter

            // Try to find existing application by name
            $applications = $this->client->applications->read(['friendlyName' => $appName], 20);
            foreach ($applications as $app) {
                if ($app->friendlyName === $appName) {
                    // Remove Voice URL if it's set - we want to use the 'To' parameter instead
                    if (! empty($app->voiceUrl)) {
                        $app->update([
                            'voiceUrl' => '',
                            'voiceMethod' => 'GET',
                        ]);
                        Log::info('Removed Voice URL from Twilio Application to use To parameter', [
                            'application_sid' => $app->sid,
                        ]);
                    }

                    return $app->sid;
                }
            }

            // Create new application WITHOUT Voice URL
            $app = $this->client->applications->create([
                'friendlyName' => $appName,
                'voiceUrl' => '', // Empty - use To parameter from device.connect()
                'voiceMethod' => 'GET',
            ]);

            Log::info('Created Twilio Application for browser calling', [
                'application_sid' => $app->sid,
                'friendly_name' => $appName,
            ]);

            return $app->sid;
        } catch (\Exception $e) {
            Log::warning('Failed to get or create Twilio Application', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
