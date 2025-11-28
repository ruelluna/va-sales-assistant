<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function delete($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        session()->flash('message', 'Campaign deleted successfully.');
    }

    public function render()
    {
        $query = Campaign::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('product_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $campaigns = $query->with('product')
            ->withCount('contacts', 'callSessions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.campaigns.campaign-list', [
            'campaigns' => $campaigns,
        ]);
    }
}
