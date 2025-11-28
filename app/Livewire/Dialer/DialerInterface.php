<?php

namespace App\Livewire\Dialer;

use App\Models\CallSession;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\TwilioService;
use Livewire\Component;

class DialerInterface extends Component
{
    public ?CallSession $activeCallSession = null;

    public $transcripts = [];

    public $currentSuggestion = null;

    public $conversationState = null;

    public $flags = [];

    public $embedded = false;

    public $initialContactId = null;

    protected $listeners = [];

    public function mount($embedded = false, $initialContactId = null)
    {
        $this->embedded = $embedded;
        $this->initialContactId = $initialContactId ? (int) $initialContactId : null;

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('DialerInterface::mount called', [
            'embedded' => $embedded,
            'initialContactId' => $this->initialContactId,
        ]);

        $this->loadActiveCall();

        // Check if a contact ID is provided via query parameter or initial contact
        $contactId = $this->initialContactId ?? request()->query('contact');

        if ($contactId) {
            // If there's an active call session but it's for a different contact, end it first
            if ($this->activeCallSession && $this->activeCallSession->contact_id !== (int) $contactId) {
                \Illuminate\Support\Facades\Log::info('Ending existing call session for different contact', [
                    'existing_contact_id' => $this->activeCallSession->contact_id,
                    'new_contact_id' => $contactId,
                ]);
                $this->endCall();
            }

            // Only call if there's no active call session (or we just ended one)
            if (! $this->activeCallSession) {
                \Illuminate\Support\Facades\Log::info('DialerInterface::mount calling callContact', [
                    'contactId' => $contactId,
                ]);
                $this->callContact((int) $contactId);
            }
        }
    }

    public function callContact($contactId)
    {
        // Ensure contactId is an integer
        $contactId = (int) $contactId;

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('DialerInterface::callContact called', [
            'contactId' => $contactId,
            'initialContactId' => $this->initialContactId,
        ]);

        $contact = Contact::with('campaign')->findOrFail($contactId);

        // Log contact details
        \Illuminate\Support\Facades\Log::info('Contact loaded for call', [
            'contact_id' => $contact->id,
            'phone' => $contact->phone,
            'campaign_id' => $contact->campaign_id,
        ]);

        // Check if contact has an active campaign
        if (! $contact->campaign_id || ! $contact->campaign || $contact->campaign->status !== 'active') {
            session()->flash('error', 'Contact must be assigned to an active campaign to call.');

            return;
        }

        // Validate phone number exists
        if (empty($contact->phone)) {
            session()->flash('error', 'Contact does not have a phone number.');

            return;
        }

        // Check if there's already an active call
        if ($this->activeCallSession) {
            session()->flash('error', 'You already have an active call. Please end it before starting a new one.');

            return;
        }

        $callSession = CallSession::create([
            'contact_id' => $contact->id,
            'campaign_id' => $contact->campaign_id,
            'va_user_id' => auth()->id(),
            'twilio_call_sid' => null,
            'direction' => 'outbound',
            'status' => 'initiated',
        ]);

        // Log call session creation
        \Illuminate\Support\Facades\Log::info('Call session created', [
            'call_session_id' => $callSession->id,
            'contact_id' => $callSession->contact_id,
        ]);

        // Load the call session with contact relationship to ensure phone number is available
        // Use fresh() to ensure we get the latest data from database
        $this->activeCallSession = CallSession::with('contact')->findOrFail($callSession->id);

        // Verify the contact phone number is correct
        \Illuminate\Support\Facades\Log::info('Active call session loaded', [
            'call_session_id' => $this->activeCallSession->id,
            'contact_id' => $this->activeCallSession->contact_id,
            'contact_phone' => $this->activeCallSession->contact->phone ?? 'N/A',
        ]);

        // Load transcripts and AI state for this specific call session
        $this->reloadTranscripts();
        $this->reloadAiState();

        // Note: For browser-based calling, the frontend JavaScript initiates the call
        // via Twilio Device SDK. The TwiML URL is fetched by Twilio to get instructions
        // on how to handle the call (dial the phone number and stream audio).
    }

    public function updateCallSid($callSid)
    {
        if ($this->activeCallSession) {
            $this->activeCallSession->update(['twilio_call_sid' => $callSid]);
            $this->activeCallSession = $this->activeCallSession->fresh();
            $this->loadActiveCall();
        }
    }

    public function endCall()
    {
        if ($this->activeCallSession) {
            $this->activeCallSession->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);
            $this->activeCallSession = null;
            $this->transcripts = [];
            $this->currentSuggestion = null;
            $this->conversationState = null;
            $this->flags = [];
        }
    }

    public function loadActiveCall()
    {
        // Clean up stale "initiated" call sessions older than 5 minutes
        CallSession::where('va_user_id', auth()->id())
            ->where('status', 'initiated')
            ->where('created_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);

        $this->activeCallSession = CallSession::with('contact')
            ->where('va_user_id', auth()->id())
            ->whereIn('status', ['initiated', 'ringing', 'in_progress'])
            ->latest()
            ->first();

        // Log for debugging
        if ($this->activeCallSession) {
            \Illuminate\Support\Facades\Log::info('loadActiveCall found session', [
                'call_session_id' => $this->activeCallSession->id,
                'contact_id' => $this->activeCallSession->contact_id,
                'contact_phone' => $this->activeCallSession->contact->phone ?? 'N/A',
            ]);
        }

        if ($this->activeCallSession) {
            $this->reloadTranscripts();
            $this->reloadAiState();
        }
    }

    public function reloadTranscripts()
    {
        \Illuminate\Support\Facades\Log::info('reloadTranscripts called', [
            'has_active_call_session' => $this->activeCallSession !== null,
            'call_session_id' => $this->activeCallSession?->id,
        ]);

        if ($this->activeCallSession) {
            // Refresh the call session to get latest data
            $this->activeCallSession = $this->activeCallSession->fresh(['contact']);

            $transcripts = $this->activeCallSession->transcripts()
                ->orderBy('timestamp', 'asc')
                ->get()
                ->map(function ($t) {
                    return [
                        'speaker' => $t->speaker,
                        'text' => $t->text,
                        'timestamp' => $t->timestamp,
                    ];
                })
                ->toArray();

            \Illuminate\Support\Facades\Log::info('Transcripts reloaded', [
                'call_session_id' => $this->activeCallSession->id,
                'transcript_count' => count($transcripts),
            ]);

            $this->transcripts = $transcripts;
        } else {
            \Illuminate\Support\Facades\Log::warning('reloadTranscripts called but no active call session');
        }
    }

    public function reloadAiState()
    {
        if ($this->activeCallSession) {
            $aiState = $this->activeCallSession->ai_state ?? [];
            $this->conversationState = $aiState['conversation_state'] ?? null;
            $this->currentSuggestion = $aiState['recommended_reply'] ?? null;
            $this->flags = $this->activeCallSession->real_time_tags ?? [];
        }
    }

    public function updatedActiveCallSession()
    {
        $this->loadActiveCall();
    }

    public function contactIdUpdated($contactId): void
    {
        $contactId = (int) $contactId;

        \Illuminate\Support\Facades\Log::info('DialerInterface::contactIdUpdated called', [
            'contactId' => $contactId,
            'current_active_call_contact_id' => $this->activeCallSession?->contact_id,
        ]);

        // If there's an active call for a different contact, end it
        if ($this->activeCallSession && $this->activeCallSession->contact_id !== $contactId) {
            $this->endCall();
        }

        // Call the new contact if we don't have an active call
        if (! $this->activeCallSession) {
            $this->callContact($contactId);
        }
    }

    public function callNext()
    {
        $contact = Contact::whereDoesntHave('callSessions', function ($query) {
            $query->where('va_user_id', auth()->id())
                ->whereIn('status', ['ringing', 'in_progress']);
        })
            ->whereHas('campaign', function ($query) {
                $query->where('status', 'active');
            })
            ->first();

        if (! $contact) {
            session()->flash('error', 'No contacts available in queue');

            return;
        }

        $this->callContact($contact->id);
    }

    public function render()
    {
        // Reload transcripts if we have an active call session
        // This ensures transcripts are always up to date, even if Echo events fail
        if ($this->activeCallSession) {
            // Only reload if we don't already have transcripts loaded
            // This prevents unnecessary database queries on every render
            if (empty($this->transcripts)) {
                $this->reloadTranscripts();
            }
        }

        $view = view('livewire.dialer.dialer-interface', [
            'campaigns' => Campaign::where('status', 'active')->get(),
        ]);

        if (! $this->embedded) {
            return $view->layout('components.layouts.app', ['title' => 'Dialer']);
        }

        return $view;
    }
}
