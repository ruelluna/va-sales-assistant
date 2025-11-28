<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ContactList extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $campaignFilter = '';
    public $showImportModal = false;
    public $importFile;
    public $importCampaignId;

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:csv,txt',
            'importCampaignId' => 'required|exists:campaigns,id',
        ]);

        $file = fopen($this->importFile->getRealPath(), 'r');
        $header = fgetcsv($file);

        $imported = 0;
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            Contact::create([
                'campaign_id' => $this->importCampaignId,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? '',
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'tags' => isset($data['tags']) ? explode(',', $data['tags']) : [],
                'timezone' => $data['timezone'] ?? null,
            ]);
            $imported++;
        }
        fclose($file);

        $this->showImportModal = false;
        $this->importFile = null;
        session()->flash('message', "Successfully imported {$imported} contacts.");
    }

    public function call($id)
    {
        $contact = Contact::with('campaign')->findOrFail($id);

        // Check if contact has an active campaign
        if (!$contact->campaign_id || !$contact->campaign || $contact->campaign->status !== 'active') {
            session()->flash('error', 'Contact must be assigned to an active campaign to call.');
            return;
        }

        // Redirect to dialer with contact ID as query parameter
        return $this->redirect(route('dialer') . '?contact=' . $id);
    }

    public function delete($id)
    {
        Contact::findOrFail($id)->delete();
        session()->flash('message', 'Contact deleted successfully.');
    }

    public function render()
    {
        $query = Contact::with('campaign');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->campaignFilter) {
            $query->where('campaign_id', $this->campaignFilter);
        }

        $contacts = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.contacts.contact-list', [
            'contacts' => $contacts,
            'campaigns' => Campaign::all(),
        ])->layout('components.layouts.app', ['title' => 'Contacts']);
    }
}
