<?php

use App\Models\CallSession;
use App\Models\Contact;
use App\Models\User;

use function Pest\Laravel\postJson;

test('twilio status webhook updates call session status', function () {
    $user = User::factory()->create(['role' => 'va']);
    $contact = Contact::factory()->create();
    $callSession = CallSession::factory()->create([
        'contact_id' => $contact->id,
        'va_user_id' => $user->id,
        'twilio_call_sid' => 'CA1234567890',
        'status' => 'initiated',
    ]);

    postJson('/api/twilio/status', [
        'CallSid' => 'CA1234567890',
        'CallStatus' => 'in-progress',
    ])->assertStatus(200);

    $callSession->refresh();
    expect($callSession->status)->toBe('in_progress');
});

test('twilio status webhook sets started_at when call goes in progress', function () {
    $user = User::factory()->create(['role' => 'va']);
    $contact = Contact::factory()->create();
    $callSession = CallSession::factory()->create([
        'contact_id' => $contact->id,
        'va_user_id' => $user->id,
        'twilio_call_sid' => 'CA1234567890',
        'status' => 'ringing',
        'started_at' => null,
    ]);

    postJson('/api/twilio/status', [
        'CallSid' => 'CA1234567890',
        'CallStatus' => 'in-progress',
    ])->assertStatus(200);

    $callSession->refresh();
    expect($callSession->started_at)->not->toBeNull();
});
