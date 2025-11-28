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
            if (! $callSessionId) {
                // Try query parameter first
                $callSessionId = $request->input('callSession') ?? $request->input('CallSession');

                // If not found, try extracting from 'To' parameter
                if (! $callSessionId) {
                    $toParam = $request->input('To');
                    if ($toParam) {
                        // Parse the URL to extract callSession query parameter
                        $parsedUrl = parse_url($toParam);
                        if (isset($parsedUrl['query'])) {
                            parse_str($parsedUrl['query'], $queryParams);
                            $callSessionId = $queryParams['callSession'] ?? null;
                        }
                    }
                }
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

            // Explicitly find the call session by ID to avoid route model binding issues
            $callSession = CallSession::with('contact')->findOrFail($callSessionIdInt);

            // Log for debugging
            Log::info('TwiML requested', [
                'requested_call_session_id' => $callSessionId,
                'resolved_call_session_id' => $callSession->id,
                'contact_id' => $callSession->contact_id,
                'contact_phone' => $callSession->contact->phone ?? 'N/A',
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
