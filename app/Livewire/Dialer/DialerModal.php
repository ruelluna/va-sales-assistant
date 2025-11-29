<?php

namespace App\Livewire\Dialer;

use Livewire\Component;

class DialerModal extends Component
{
    public $show = false;

    public $contactId = null;

    public bool $shouldMock = false;

    protected $listeners = ['openDialer', 'closeDialer'];

    public function openDialer($contactId = null, $shouldMock = false): void
    {
        // Handle Livewire 3 named parameters - they come as an array
        if (is_array($contactId)) {
            $shouldMock = $contactId['shouldMock'] ?? false;
            $contactId = $contactId['contactId'] ?? $contactId[0] ?? null;
        }

        // Ensure contactId is an integer
        $newContactId = $contactId ? (int) $contactId : null;

        // Verify contact exists and get its phone number for logging
        $contactPhone = null;
        $contactName = null;
        if ($newContactId) {
            try {
                $contact = \App\Models\Contact::find($newContactId);
                if ($contact) {
                    $contactPhone = $contact->phone;
                    $contactName = $contact->full_name;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not load contact in DialerModal', [
                    'contact_id' => $newContactId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('DialerModal::openDialer called', [
            'received_contactId' => $contactId,
            'parsed_contactId' => $newContactId,
            'previous_contactId' => $this->contactId,
            'contact_phone' => $contactPhone,
            'contact_name' => $contactName,
            'should_mock' => (bool) $shouldMock,
        ]);

        // CRITICAL: Verify we're not accidentally reusing an old contact ID
        if ($this->contactId && $this->contactId !== $newContactId && $newContactId) {
            \Illuminate\Support\Facades\Log::warning('DialerModal: Contact ID changed', [
                'old_contact_id' => $this->contactId,
                'new_contact_id' => $newContactId,
            ]);
        }

        $this->contactId = $newContactId;
        $this->shouldMock = (bool) $shouldMock;
        $this->show = true;
    }

    public function closeDialer(): void
    {
        $this->show = false;
        $this->contactId = null;
        $this->shouldMock = false;
    }

    public function render()
    {
        return view('livewire.dialer.dialer-modal');
    }
}
