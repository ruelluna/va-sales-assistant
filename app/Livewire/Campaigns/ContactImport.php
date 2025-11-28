<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\Contact;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContactImport extends Component
{
    use WithFileUploads;

    public Campaign $campaign;
    public $importFile;

    public function mount($id)
    {
        $this->campaign = Campaign::findOrFail($id);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:csv,txt',
        ]);

        $file = fopen($this->importFile->getRealPath(), 'r');
        $header = fgetcsv($file);

        $imported = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            Contact::create([
                'campaign_id' => $this->campaign->id,
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

        session()->flash('message', "Successfully imported {$imported} contacts.");
        return $this->redirect(route('campaigns.index'));
    }

    public function render()
    {
        return view('livewire.campaigns.contact-import')
            ->layout('components.layouts.app', ['title' => 'Import Contacts - ' . $this->campaign->name]);
    }
}
