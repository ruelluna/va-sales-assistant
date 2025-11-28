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
        $request->validate([
            'call_session_id' => 'required|exists:call_sessions,id',
            'speaker' => 'required|in:va,prospect,system',
            'text' => 'required|string',
            'timestamp' => 'required|numeric|min:0',
        ]);

        $callSession = CallSession::findOrFail($request->input('call_session_id'));

        $transcript = $callSession->transcripts()->create([
            'speaker' => $request->input('speaker'),
            'text' => $request->input('text'),
            'timestamp' => $request->input('timestamp'),
        ]);

        event(new CallTranscriptUpdated($callSession, $transcript));

        if ($request->input('speaker') === 'prospect' && $callSession->isActive()) {
            GenerateSuggestionForCallSession::dispatch($callSession);
        }

        return response()->json(['message' => 'Transcript stored'], 201);
    }
}
