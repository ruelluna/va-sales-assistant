<?php

use App\Models\CallSession;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('can create a call session', function () {
    $user = User::factory()->create(['role' => 'va']);
    $campaign = Campaign::factory()->create();
    $contact = Contact::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    $callSession = CallSession::factory()->create([
        'contact_id' => $contact->id,
        'campaign_id' => $campaign->id,
        'va_user_id' => $user->id,
        'status' => 'initiated',
    ]);

    expect($callSession)->toBeInstanceOf(CallSession::class);
    expect($callSession->contact_id)->toBe($contact->id);
    expect($callSession->va_user_id)->toBe($user->id);
});

test('call session has transcripts relationship', function () {
    $callSession = CallSession::factory()->create();
    $transcript = $callSession->transcripts()->create([
        'speaker' => 'prospect',
        'text' => 'Hello',
        'timestamp' => 0.0,
    ]);

    expect($callSession->transcripts)->toHaveCount(1);
    expect($callSession->transcripts->first()->text)->toBe('Hello');
});

test('call session is active when in progress', function () {
    $callSession = CallSession::factory()->create(['status' => 'in_progress']);
    expect($callSession->isActive())->toBeTrue();

    $callSession->update(['status' => 'completed']);
    expect($callSession->isActive())->toBeFalse();
});
