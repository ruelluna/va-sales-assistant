<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\Campaign;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContactForm extends Component
{
    use AuthorizesRequests;

    public Contact $contact;
    public $campaign_id = null;
    public $first_name = '';
    public $last_name = '';
    public $phone = '';
    public $email = '';
    public $company = '';
    public $tags = '';
    public $timezone = '';

    public function mount($id = null): void
    {
        if ($id) {
            $this->contact = Contact::with('campaign')->findOrFail($id);
            $this->authorize('edit', $this->contact);

            $this->campaign_id = $this->contact->campaign_id;
            $this->first_name = $this->contact->first_name;
            $this->last_name = $this->contact->last_name;
            $this->phone = $this->contact->phone;
            $this->email = $this->contact->email ?? '';
            $this->company = $this->contact->company ?? '';
            $this->tags = is_array($this->contact->tags) ? implode(', ', $this->contact->tags) : ($this->contact->tags ?? '');
            $this->timezone = $this->contact->timezone ?? '';
        } else {
            $this->contact = new Contact();
        }
    }

    public function save()
    {
        if ($this->contact->exists) {
            $this->authorize('update', $this->contact);
        }

        $this->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'timezone' => 'nullable|string|max:255',
        ]);

        $tagsArray = [];
        if (!empty($this->tags)) {
            $tagsArray = array_map('trim', explode(',', $this->tags));
            $tagsArray = array_filter($tagsArray);
        }

        $this->contact->fill([
            'campaign_id' => $this->campaign_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'company' => $this->company ?: null,
            'tags' => $tagsArray,
            'timezone' => $this->timezone ?: null,
        ])->save();

        session()->flash('message', $this->contact->wasRecentlyCreated ? 'Contact created successfully.' : 'Contact updated successfully.');

        return $this->redirect(route('contacts.index'));
    }

    public function render()
    {
        return view('livewire.contacts.contact-form', [
            'campaigns' => Campaign::all(),
        ])->layout('components.layouts.app', ['title' => $this->contact->exists ? 'Edit Contact' : 'Create Contact']);
    }
}
