<?php

namespace App\Events;

use App\Models\CallSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStateUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CallSession $callSession,
        public string $status
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
            'status' => $this->status,
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.state.updated';
    }
}
