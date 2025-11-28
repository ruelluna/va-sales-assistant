<?php

namespace App\Http\Controllers\Api;

use App\Events\CallTranscriptUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateSuggestionForCallSession;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaStreamController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Transcript received from media stream service', [
            'call_session_id' => $request->input('call_session_id'),
            'speaker' => $request->input('speaker'),
            'text_length' => strlen($request->input('text', '')),
            'timestamp' => $request->input('timestamp'),
            'ip' => $request->ip(),
        ]);

        try {
            $request->validate([
                'call_session_id' => 'required|exists:call_sessions,id',
                'speaker' => 'required|in:va,prospect,system',
                'text' => 'required|string',
                'timestamp' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Transcript validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        try {
            $callSession = CallSession::findOrFail($request->input('call_session_id'));

            $transcript = $callSession->transcripts()->create([
                'speaker' => $request->input('speaker'),
                'text' => $request->input('text'),
                'timestamp' => $request->input('timestamp'),
            ]);

            Log::info('Transcript stored successfully', [
                'call_session_id' => $callSession->id,
                'transcript_id' => $transcript->id,
                'speaker' => $transcript->speaker,
                'text_preview' => substr($transcript->text, 0, 50),
            ]);

            event(new CallTranscriptUpdated($callSession, $transcript));
        } catch (\Exception $e) {
            Log::error('Error storing transcript', [
                'call_session_id' => $request->input('call_session_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        if ($request->input('speaker') === 'prospect' && $callSession->isActive()) {
            Log::info('Dispatching AI suggestion job for prospect transcript', [
                'call_session_id' => $callSession->id,
            ]);
            GenerateSuggestionForCallSession::dispatch($callSession);
        }

        return response()->json(['message' => 'Transcript stored'], 201);
    }
}
