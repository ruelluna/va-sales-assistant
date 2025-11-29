<?php

namespace App\Http\Controllers\Api;

use App\Events\CallStateUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\SummarizeCallSession;
use App\Models\CallSession;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    public function status(Request $request)
    {
        $callSid = $request->input('CallSid');
        $callStatus = $request->input('CallStatus');

        $callSession = CallSession::where('twilio_call_sid', $callSid)->first();

        if (! $callSession) {
            Log::warning('Twilio status callback for unknown call', [
                'call_sid' => $callSid,
                'status' => $callStatus,
            ]);

            return response()->json(['message' => 'Call session not found'], 404);
        }

        $statusMap = [
            'initiated' => 'initiated',
            'ringing' => 'ringing',
            'in-progress' => 'in_progress',
            'completed' => 'completed',
            'failed' => 'failed',
            'no-answer' => 'no_answer',
            'busy' => 'failed',
            'canceled' => 'failed',
        ];

        $newStatus = $statusMap[$callStatus] ?? $callSession->status;
        $callSession->status = $newStatus;

        if ($callStatus === 'in-progress' && ! $callSession->started_at) {
            $callSession->started_at = now();
        }

        if (in_array($callStatus, ['completed', 'failed', 'no-answer', 'busy', 'canceled'])) {
            $callSession->ended_at = now();
            if ($callSession->started_at) {
                $callSession->duration_seconds = $callSession->started_at->diffInSeconds($callSession->ended_at);
            }
        }

        $callSession->save();

        event(new CallStateUpdated($callSession, $newStatus));

        if (in_array($callStatus, ['completed', 'failed', 'no-answer', 'busy', 'canceled'])) {
            SummarizeCallSession::dispatch($callSession);
        }

        return response()->json(['message' => 'Status updated']);
    }

    public function twiml(Request $request, $callSessionId = null)
    {
        try {
            // Support multiple ways to get call session ID:
            // 1. Route parameter: /api/twilio/twiml/31
            // 2. Query parameter: /api/twilio/twiml?callSession=31
            // 3. From 'To' parameter (when Twilio calls the Application Voice URL):
            //    The 'To' parameter contains the full URL like: https://.../api/twilio/twiml?callSession=32
            $expectedContactId = null;

            $phoneNumberFromUrl = null;

            if (! $callSessionId) {
                // Try query parameter first
                $callSessionId = $request->input('callSession') ?? $request->input('CallSession');
                $expectedContactId = $request->input('contactId');
                $phoneNumberFromUrl = $request->input('phoneNumber');

                // If not found, try extracting from 'To' parameter
                if (! $callSessionId) {
                    $toParam = $request->input('To');
                    if ($toParam) {
                        // Parse the URL to extract callSession, contactId, and phoneNumber query parameters
                        $parsedUrl = parse_url($toParam);
                        if (isset($parsedUrl['query'])) {
                            parse_str($parsedUrl['query'], $queryParams);
                            $callSessionId = $queryParams['callSession'] ?? null;
                            $expectedContactId = $queryParams['contactId'] ?? null;
                            $phoneNumberFromUrl = $queryParams['phoneNumber'] ?? null;
                        }
                    }
                }
            } else {
                // If callSessionId came from route, try to get contactId and phoneNumber from query
                $expectedContactId = $request->input('contactId');
                $phoneNumberFromUrl = $request->input('phoneNumber');
            }

            // Convert to integer
            $callSessionIdInt = is_numeric($callSessionId) ? (int) $callSessionId : null;

            if (! $callSessionIdInt) {
                Log::error('TwiML requested without valid call session ID', [
                    'url' => $request->fullUrl(),
                    'query_params' => $request->all(),
                    'to_param' => $request->input('To'),
                    'call_session_id' => $callSessionId,
                ]);
                $response = new \Twilio\TwiML\VoiceResponse;
                $response->say('Sorry, an application error occurred. Goodbye.', ['voice' => 'alice']);

                return response($response->asXML(), 200)->header('Content-Type', 'text/xml');
            }

            // CRITICAL: Query the database directly to ensure we get the exact call session
            // Don't use Eloquent findOrFail() as it might use cached data
            $callSessionData = \Illuminate\Support\Facades\DB::table('call_sessions')
                ->where('id', $callSessionIdInt)
                ->first();

            if (! $callSessionData) {
                Log::error('TwiML requested for non-existent call session', [
                    'requested_call_session_id' => $callSessionIdInt,
                ]);
                $response = new \Twilio\TwiML\VoiceResponse;
                $response->say('Sorry, call session not found. Goodbye.', ['voice' => 'alice']);

                return response($response->asXML(), 200)->header('Content-Type', 'text/xml');
            }

            // CRITICAL: Use the expected contact_id from URL if provided, otherwise use call session's contact_id
            // This ensures we always dial the correct number even if call session has wrong contact_id
            $contactIdToUse = $expectedContactId ? (int) $expectedContactId : $callSessionData->contact_id;

            if ($expectedContactId && (int) $expectedContactId !== $callSessionData->contact_id) {
                Log::warning('CRITICAL: Call session contact_id mismatch with URL parameter', [
                    'call_session_id' => $callSessionIdInt,
                    'call_session_contact_id' => $callSessionData->contact_id,
                    'expected_contact_id_from_url' => $expectedContactId,
                    'using_contact_id' => $contactIdToUse,
                ]);
            }

            // CRITICAL: Get the contact directly from database using the contact_id we determined
            $contactData = \Illuminate\Support\Facades\DB::table('contacts')
                ->where('id', $contactIdToUse)
                ->first();

            if (! $contactData) {
                Log::error('CRITICAL: Contact not found', [
                    'call_session_id' => $callSessionIdInt,
                    'call_session_contact_id' => $callSessionData->contact_id,
                    'requested_contact_id' => $contactIdToUse,
                ]);
                $response = new \Twilio\TwiML\VoiceResponse;
                $response->say('Sorry, contact not found. Goodbye.', ['voice' => 'alice']);

                return response($response->asXML(), 200)->header('Content-Type', 'text/xml');
            }

            // CRITICAL: Determine the phone number to dial BEFORE loading call session
            // Priority: 1) phoneNumberFromUrl, 2) contactData from database
            $phoneNumberToDial = $phoneNumberFromUrl ?: ($contactData->phone ?? null);

            if (empty($phoneNumberToDial)) {
                Log::error('CRITICAL: No phone number available to dial', [
                    'call_session_id' => $callSessionIdInt,
                    'phone_number_from_url' => $phoneNumberFromUrl,
                    'contact_data_phone' => $contactData->phone ?? null,
                ]);
                $response = new \Twilio\TwiML\VoiceResponse;
                $response->say('Sorry, phone number is missing. Goodbye.', ['voice' => 'alice']);

                return response($response->asXML(), 200)->header('Content-Type', 'text/xml');
            }

            // Load the call session WITHOUT eager loading contact if we're going to override it
            // This prevents loading the wrong contact that would be cached
            if ($phoneNumberFromUrl || $expectedContactId) {
                // Don't eager load contact - we'll set a temp one
                $callSession = CallSession::findOrFail($callSessionData->id);

                // CRITICAL: Create a temporary contact object with the phone number we're going to dial
                // This ensures TwilioService always uses the correct phone number
                // Use the Contact model to ensure all required attributes are available
                $tempContact = new \App\Models\Contact;
                $tempContact->id = $contactIdToUse;
                $tempContact->phone = $phoneNumberToDial;
                $tempContact->full_name = $contactData->full_name ?? null;
                $tempContact->exists = true; // Mark as existing to prevent save attempts
                $callSession->setRelation('contact', $tempContact);
            } else {
                // Load with correct contact if no URL override needed
                $callSession = CallSession::with('contact')->findOrFail($callSessionData->id);

                // Still override if contact_id doesn't match
                if ($callSession->contact_id !== $contactIdToUse) {
                    $correctContact = \App\Models\Contact::find($contactIdToUse);
                    if ($correctContact) {
                        $callSession->setRelation('contact', $correctContact);
                    }
                }
            }

            // Log for debugging - CRITICAL for production debugging
            // Safely get contact info to avoid exceptions
            $contactPhone = null;
            $contactIdFromRelation = null;
            if ($callSession->relationLoaded('contact') && $callSession->contact) {
                $contactPhone = $callSession->contact->phone ?? null;
                $contactIdFromRelation = $callSession->contact->id ?? null;
            }

            Log::info('TwiML requested', [
                'requested_call_session_id' => $callSessionId,
                'resolved_call_session_id' => $callSession->id,
                'call_session_contact_id' => $callSessionData->contact_id,
                'expected_contact_id_from_url' => $expectedContactId,
                'contact_id_to_use' => $contactIdToUse,
                'phone_number_from_url' => $phoneNumberFromUrl,
                'phone_number_from_db' => $contactData->phone ?? null,
                'phone_number_to_dial' => $phoneNumberToDial,
                'call_session_contact_phone' => $contactPhone ?? 'NOT SET',
                'call_session_contact_id_from_relation' => $contactIdFromRelation ?? 'NOT SET',
                'contact_relation_loaded' => $callSession->relationLoaded('contact'),
                'va_user_id' => $callSession->va_user_id,
                'status' => $callSession->status,
            ]);

            $twilioService = app(\App\Services\TwilioService::class);
            $twiml = $twilioService->generateTwiML($callSession);

            // Log the generated TwiML
            Log::info('TwiML generated', [
                'call_session_id' => $callSession->id,
                'twiml_length' => strlen($twiml),
            ]);

            return response($twiml, 200)
                ->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {
            Log::error('Error generating TwiML', [
                'requested_call_session_id' => $callSessionId ?? 'unknown',
                'call_session_id_int' => $callSessionIdInt ?? null,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
                'request_params' => $request->all(),
            ]);

            // Return error TwiML
            $response = new \Twilio\TwiML\VoiceResponse;
            $response->say('Sorry, an application error occurred. Goodbye.', ['voice' => 'alice']);

            return response($response->asXML(), 200)
                ->header('Content-Type', 'text/xml');
        }
    }

    public function token(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $twilioService = app(TwilioService::class);
            $token = $twilioService->generateAccessToken($user->id);

            return response()->json([
                'token' => $token,
                'identity' => (string) $user->id,
            ]);
        } catch (\RuntimeException $e) {
            Log::error('Failed to generate Twilio access token', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to generate Twilio access token', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to generate access token: '.$e->getMessage(),
            ], 500);
        }
    }
}
