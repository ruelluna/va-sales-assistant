<?php

namespace App\Livewire\Dialer;

use App\Events\CallTranscriptUpdated;
use App\Models\CallSession;
use App\Models\Campaign;
use App\Models\Contact;
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

    public bool $shouldMock = false;

    protected $listeners = ['contactIdUpdated'];

    public function contactIdUpdated($contactId): void
    {
        $contactId = (int) $contactId;

        \Illuminate\Support\Facades\Log::info('DialerInterface::contactIdUpdated called', [
            'contactId' => $contactId,
            'current_initialContactId' => $this->initialContactId,
            'current_active_call_contact_id' => $this->activeCallSession?->contact_id,
        ]);

        // If the contact ID changed, update it and restart the call process
        if ($this->initialContactId !== $contactId) {
            \Illuminate\Support\Facades\Log::info('Contact ID changed, updating and restarting call', [
                'old_contact_id' => $this->initialContactId,
                'new_contact_id' => $contactId,
            ]);

            $this->initialContactId = $contactId;

            // End any existing call for a different contact
            if ($this->activeCallSession && $this->activeCallSession->contact_id !== $contactId) {
                $this->endCall();
            }

            // Call the new contact
            $this->callContact($contactId);
        }
    }

    private const MOCK_TRANSCRIPTS = [
        ['speaker' => 'system', 'text' => 'Mock call connected.', 'timestamp' => 1],
        ['speaker' => 'va', 'text' => 'Hi there, this is a mock VA checking in.', 'timestamp' => 3],
        ['speaker' => 'prospect', 'text' => 'Hello! I can hear you loud and clear.', 'timestamp' => 5],
        ['speaker' => 'va', 'text' => 'Great. This transcript only lives locally.', 'timestamp' => 7],
        ['speaker' => 'prospect', 'text' => 'Perfect—looks like everything works.', 'timestamp' => 9],
    ];

    public function mount($embedded = false, $initialContactId = null, $shouldMock = false)
    {
        $this->embedded = $embedded;
        $this->initialContactId = $initialContactId ? (int) $initialContactId : null;
        $this->shouldMock = (bool) $shouldMock;

        // Log for debugging
        \Illuminate\Support\Facades\Log::info('DialerInterface::mount called', [
            'embedded' => $embedded,
            'initialContactId' => $this->initialContactId,
        ]);

        // Check if a contact ID is provided via query parameter or initial contact
        $contactId = $this->initialContactId ?? request()->query('contact');

        if ($contactId) {
            $contactId = (int) $contactId;

            // CRITICAL: Always end ALL active call sessions and clear the property
            // We NEVER want to reuse old call sessions - always create fresh ones
            $endedSessions = CallSession::where('va_user_id', auth()->id())
                ->whereIn('status', ['initiated', 'ringing'])
                ->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);

            if ($endedSessions > 0) {
                \Illuminate\Support\Facades\Log::info('Ended ALL active call sessions in mount', [
                    'ended_count' => $endedSessions,
                    'target_contact_id' => $contactId,
                ]);
            }

            // Clear the active call session property to ensure we start fresh
            $this->activeCallSession = null;

            // Always create a fresh call session - never reuse old ones
            \Illuminate\Support\Facades\Log::info('DialerInterface::mount calling callContact', [
                'contactId' => $contactId,
            ]);
            $this->callContact($contactId);
        } else {
            // No contact ID provided, just load active call normally
            $this->loadActiveCall();
        }

        if ($this->shouldMock && $this->activeCallSession) {
            $this->sendMockTranscripts();
            $this->shouldMock = false;
        }
    }

    public function callContact($contactId)
    {
        // Ensure contactId is an integer
        $contactId = (int) $contactId;

        // Log for debugging - CRITICAL for tracing the issue
        \Illuminate\Support\Facades\Log::info('DialerInterface::callContact called', [
            'contactId' => $contactId,
            'contactId_type' => gettype($contactId),
            'initialContactId' => $this->initialContactId,
            'initialContactId_type' => gettype($this->initialContactId),
            'current_active_call_contact_id' => $this->activeCallSession?->contact_id,
            'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
        ]);

        // CRITICAL: End ALL initiated/ringing call sessions for THIS user, regardless of contact
        // This ensures we NEVER reuse an old call session, even if it's for the same contact
        // We always want a fresh call session for each new call attempt
        $endedSessions = CallSession::where('va_user_id', auth()->id())
            ->whereIn('status', ['initiated', 'ringing'])
            ->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);

        if ($endedSessions > 0) {
            \Illuminate\Support\Facades\Log::info('Ended ALL active call sessions before creating new one', [
                'ended_count' => $endedSessions,
                'target_contact_id' => $contactId,
            ]);
        }

        // Clear the active call session property to ensure we start fresh
        $this->activeCallSession = null;

        $contact = Contact::with('campaign')->findOrFail($contactId);

        // CRITICAL: Verify the loaded contact matches the requested contact ID
        if ($contact->id !== $contactId) {
            \Illuminate\Support\Facades\Log::error('CRITICAL: Loaded contact ID does not match requested contact ID', [
                'requested_contact_id' => $contactId,
                'loaded_contact_id' => $contact->id,
                'loaded_contact_phone' => $contact->phone,
            ]);
            session()->flash('error', 'Contact ID mismatch detected. Please try again.');

            return;
        }

        // Log contact details
        \Illuminate\Support\Facades\Log::info('Contact loaded for call', [
            'contact_id' => $contact->id,
            'phone' => $contact->phone,
            'campaign_id' => $contact->campaign_id,
            'requested_contact_id' => $contactId,
            'match' => $contact->id === $contactId,
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

        // CRITICAL: Log exactly what we're about to create
        $callSessionData = [
            'contact_id' => $contact->id,
            'campaign_id' => $contact->campaign_id,
            'va_user_id' => auth()->id(),
            'twilio_call_sid' => null,
            'direction' => 'outbound',
            'status' => 'initiated',
        ];

        \Illuminate\Support\Facades\Log::info('About to create call session', [
            'data' => $callSessionData,
            'contact_id_from_contact' => $contact->id,
            'requested_contact_id' => $contactId,
            'match' => $contact->id === $contactId,
        ]);

        // CRITICAL: Use DB::table to ensure we're creating with the exact values we want
        $callSessionId = \Illuminate\Support\Facades\DB::table('call_sessions')->insertGetId($callSessionData);

        // Load the created call session
        $callSession = CallSession::with('contact')->findOrFail($callSessionId);

        // CRITICAL: Immediately verify it was created correctly
        if ($callSession->contact_id !== $contactId) {
            \Illuminate\Support\Facades\Log::error('CRITICAL: Call session created with wrong contact_id', [
                'expected_contact_id' => $contactId,
                'actual_contact_id' => $callSession->contact_id,
                'call_session_id' => $callSession->id,
                'contact_phone_requested' => $contact->phone,
                'contact_phone_in_db' => $callSession->contact->phone ?? 'N/A',
            ]);

            // Delete the incorrectly created call session
            $callSession->delete();
            session()->flash('error', 'Error creating call session. Please try again.');

            return;
        }

        // Log call session creation
        \Illuminate\Support\Facades\Log::info('Call session created and verified', [
            'call_session_id' => $callSession->id,
            'contact_id' => $callSession->contact_id,
            'contact_phone' => $contact->phone,
            'expected_contact_id' => $contactId,
        ]);

        // CRITICAL: Load the call session DIRECTLY by ID - don't use loadActiveCall() which might find a different session
        // Use fresh() to ensure we get the latest data from database
        $this->activeCallSession = CallSession::with('contact')->findOrFail($callSession->id);

        // CRITICAL: Verify the loaded call session matches what we just created
        if ($this->activeCallSession->id !== $callSession->id) {
            \Illuminate\Support\Facades\Log::error('CRITICAL: Wrong call session loaded', [
                'expected_call_session_id' => $callSession->id,
                'loaded_call_session_id' => $this->activeCallSession->id,
                'expected_contact_id' => $contactId,
                'loaded_contact_id' => $this->activeCallSession->contact_id,
            ]);
            // Delete the wrong session and try again
            $this->activeCallSession->delete();
            session()->flash('error', 'Error loading call session. Please try again.');

            return;
        }

        // CRITICAL: Verify the contact_id matches what we expect
        if ($this->activeCallSession->contact_id !== $contactId) {
            \Illuminate\Support\Facades\Log::error('CRITICAL: Call session has wrong contact_id after loading', [
                'call_session_id' => $this->activeCallSession->id,
                'expected_contact_id' => $contactId,
                'actual_contact_id' => $this->activeCallSession->contact_id,
                'expected_phone' => $contact->phone,
                'actual_phone' => $this->activeCallSession->contact->phone ?? 'N/A',
            ]);
            // Delete the wrong session and try again
            $this->activeCallSession->delete();
            session()->flash('error', 'Call session has wrong contact. Please try again.');

            return;
        }

        // Double-verify the contact phone number matches what we expect
        $loadedContactPhone = $this->activeCallSession->contact->phone ?? null;
        if ($loadedContactPhone !== $contact->phone) {
            \Illuminate\Support\Facades\Log::error('CRITICAL: Contact phone mismatch after loading call session', [
                'call_session_id' => $this->activeCallSession->id,
                'contact_id' => $this->activeCallSession->contact_id,
                'expected_phone' => $contact->phone,
                'loaded_phone' => $loadedContactPhone,
            ]);
        }

        // Verify the contact phone number is correct
        \Illuminate\Support\Facades\Log::info('Active call session loaded and verified', [
            'call_session_id' => $this->activeCallSession->id,
            'contact_id' => $this->activeCallSession->contact_id,
            'contact_phone' => $this->activeCallSession->contact->phone ?? 'N/A',
            'contact_name' => $this->activeCallSession->contact->full_name ?? 'N/A',
            'expected_contact_id' => $contactId,
            'match' => $this->activeCallSession->contact_id === $contactId,
        ]);

        // Load transcripts and AI state for this specific call session
        $this->reloadTranscripts();
        $this->reloadAiState();

        if ($this->shouldMock) {
            $this->sendMockTranscripts();
            $this->shouldMock = false;
        }

        // Note: For browser-based calling, the frontend JavaScript initiates the call
        // via Twilio Device SDK. The TwiML URL is fetched by Twilio to get instructions
        // on how to handle the call (dial the phone number and stream audio).
    }

    public function updateCallSid($callSid)
    {
        if ($this->activeCallSession) {
            $currentCallSessionId = $this->activeCallSession->id;
            $currentContactId = $this->activeCallSession->contact_id;

            $this->activeCallSession->update(['twilio_call_sid' => $callSid]);
            $this->activeCallSession = $this->activeCallSession->fresh(['contact']);

            // CRITICAL: Verify we're still using the same call session
            if ($this->activeCallSession->id !== $currentCallSessionId) {
                \Illuminate\Support\Facades\Log::error('CRITICAL: Call session changed after updateCallSid', [
                    'expected_call_session_id' => $currentCallSessionId,
                    'actual_call_session_id' => $this->activeCallSession->id,
                    'expected_contact_id' => $currentContactId,
                    'actual_contact_id' => $this->activeCallSession->contact_id,
                ]);
                // Reload the correct call session
                $this->activeCallSession = CallSession::with('contact')->findOrFail($currentCallSessionId);
            }

            // Don't call loadActiveCall() here - it might find a different call session
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

            if ($this->shouldMock) {
                $this->sendMockTranscripts();
                $this->shouldMock = false;
            }
        }
    }

    /**
     * Add a single transcript directly from event payload (fast, no DB query)
     * This method prevents duplicates by checking if transcript already exists
     */
    public function addTranscript($speaker, $text, $timestamp): void
    {
        if (! $this->activeCallSession) {
            return;
        }

        // Normalize timestamp to float for comparison
        $timestamp = (float) ($timestamp ?? 0);

        // Check if this transcript already exists (prevent duplicates)
        $exists = false;
        foreach ($this->transcripts as $existing) {
            if (
                ($existing['speaker'] ?? '') === $speaker &&
                ($existing['text'] ?? '') === $text &&
                abs(($existing['timestamp'] ?? 0) - $timestamp) < 0.1
            ) {
                $exists = true;
                break;
            }
        }

        if (! $exists) {
            // Add transcript to array directly (faster than DB query)
            $this->transcripts[] = [
                'speaker' => $speaker,
                'text' => $text,
                'timestamp' => $timestamp,
            ];

            // Sort by timestamp to maintain order
            usort($this->transcripts, function ($a, $b) {
                return ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0);
            });
        }
    }

    /**
     * Reload all transcripts from database (used for initial load and fallback)
     */
    public function reloadTranscripts()
    {
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

            // Reset and update transcripts to ensure Livewire detects the change
            $this->transcripts = [];
            $this->transcripts = $transcripts;
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

    public function sendMockTranscripts(): void
    {
        if (app()->environment('production')) {
            abort(403, 'Mock transcripts are disabled in production.');
        }

        if (! $this->activeCallSession) {
            session()->flash('error', 'Start a call before sending mock transcripts.');

            return;
        }

        foreach (self::MOCK_TRANSCRIPTS as $mockData) {
            $transcript = $this->activeCallSession->transcripts()->create($mockData);
            event(new CallTranscriptUpdated($this->activeCallSession, $transcript));
        }

        $this->reloadTranscripts();
        session()->flash('success', 'Mock transcripts pushed to the call.');
    }

    public function render()
    {
        // Note: wire:poll handles transcript reloading automatically
        // This method just renders the view with current state

        $view = view('livewire.dialer.dialer-interface', [
            'campaigns' => Campaign::where('status', 'active')->get(),
        ]);

        if (! $this->embedded) {
            return $view->layout('components.layouts.app', ['title' => 'Dialer']);
        }

        return $view;
    }
}
