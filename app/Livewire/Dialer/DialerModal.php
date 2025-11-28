<?php

namespace App\Livewire\Dialer;

use Livewire\Component;

class DialerModal extends Component
{
    public $show = false;

    public $contactId = null;

    protected $listeners = ['openDialer', 'closeDialer'];

    public function openDialer($contactId = null): void
    {
        // Handle Livewire 3 named parameters - they come as an array
        if (is_array($contactId)) {
            $contactId = $contactId['contactId'] ?? $contactId[0] ?? null;
        }

        // Ensure contactId is an integer
        $newContactId = $contactId ? (int) $contactId : null;

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('DialerModal::openDialer called', [
            'received_contactId' => $contactId,
            'parsed_contactId' => $newContactId,
            'previous_contactId' => $this->contactId,
        ]);

        $this->contactId = $newContactId;
        $this->show = true;
    }

    public function closeDialer(): void
    {
        $this->show = false;
        $this->contactId = null;
    }

    public function render()
    {
        return view('livewire.dialer.dialer-modal');
    }
}
