<?php

namespace App\Events;

use App\Models\CallSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiSuggestionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CallSession $callSession,
        public string $conversationState,
        public string $recommendedReply,
        public array $flags = []
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
            'conversation_state' => $this->conversationState,
            'recommended_reply' => $this->recommendedReply,
            'flags' => $this->flags,
        ];
    }

    public function broadcastAs(): string
    {
        return 'ai.suggestion.updated';
    }
}
