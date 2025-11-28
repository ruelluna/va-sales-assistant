<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Contact;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignDetail extends Component
{
    use WithPagination;

    public Campaign $campaign;

    public $search = '';

    public $showNoteModal = false;

    public $noteContactId = null;

    public $note = '';

    public $callingContactId = null;

    public function mount($id): void
    {
        $this->campaign = Campaign::with(['product'])->findOrFail($id);
    }

    public function openNoteModal($contactId): void
    {
        $this->noteContactId = (int) $contactId;
        $this->note = '';
        $this->showNoteModal = true;
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal = false;
        $this->noteContactId = null;
        $this->note = '';
    }

    public function saveNote(): void
    {
        $this->validate([
            'note' => 'required|string|min:1',
            'noteContactId' => 'required|exists:contacts,id',
        ]);

        \App\Models\ContactNote::create([
            'contact_id' => $this->noteContactId,
            'user_id' => auth()->id(),
            'note' => $this->note,
        ]);

        $this->closeNoteModal();
        session()->flash('message', 'Note added successfully.');
    }

    public function callContact($contactId): void
    {
        $contactId = (int) $contactId;
        $this->callingContactId = $contactId;

        $contact = Contact::with('campaign')->findOrFail($contactId);

        if (! $contact->campaign_id || ! $contact->campaign || $contact->campaign->status !== 'active') {
            session()->flash('error', 'Contact must be assigned to an active campaign to call.');
            $this->callingContactId = null;

            return;
        }

        $this->dispatch('openDialer', contactId: $contactId);
        $this->callingContactId = null;
    }

    public function render()
    {
        $query = $this->campaign->contacts();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $contacts = $query->withCount('callSessions')
            ->with('latestNote')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.campaigns.campaign-detail', [
            'contacts' => $contacts,
        ])->layout('components.layouts.app', ['title' => $this->campaign->name]);
    }
}
