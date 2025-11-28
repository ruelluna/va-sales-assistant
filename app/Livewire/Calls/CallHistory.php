<?php

namespace App\Livewire\Calls;

use App\Models\CallSession;
use Livewire\Component;
use Livewire\WithPagination;

class CallHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $outcomeFilter = '';
    public $campaignFilter = '';

    public function render()
    {
        $query = CallSession::with(['contact', 'campaign', 'vaUser']);

        if (auth()->user()->role === 'va') {
            $query->where('va_user_id', auth()->id());
        }

        if ($this->search) {
            $query->whereHas('contact', function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->outcomeFilter) {
            $query->where('outcome', $this->outcomeFilter);
        }

        if ($this->campaignFilter) {
            $query->where('campaign_id', $this->campaignFilter);
        }

        $calls = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.calls.call-history', [
            'calls' => $calls,
            'campaigns' => \App\Models\Campaign::all(),
        ])->layout('components.layouts.app', ['title' => 'Call History']);
    }
}
