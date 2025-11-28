<?php

namespace App\Events;

use App\Models\CallSession;
use App\Models\CallTranscript;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallTranscriptUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CallSession $callSession,
        public CallTranscript $transcript
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("call-session.{$this->callSession->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'call_session_id' => $this->callSession->id,
            'speaker' => $this->transcript->speaker,
            'text' => $this->transcript->text,
            'timestamp' => $this->transcript->timestamp,
            'created_at' => $this->transcript->created_at->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transcript.updated';
    }
}
