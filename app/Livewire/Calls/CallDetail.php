<?php

namespace App\Livewire\Calls;

use App\Models\CallSession;
use Livewire\Component;

class CallDetail extends Component
{
    public CallSession $callSession;

    public function mount($id)
    {
        $this->callSession = CallSession::with(['contact', 'campaign', 'vaUser', 'transcripts'])
            ->findOrFail($id);

        if (auth()->user()->role === 'va' && $this->callSession->va_user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.calls.call-detail')
            ->layout('components.layouts.app', ['title' => 'Call Detail']);
    }
}
