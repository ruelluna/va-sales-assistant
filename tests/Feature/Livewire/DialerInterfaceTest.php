<?php

declare(strict_types=1);

use App\Events\CallTranscriptUpdated;
use App\Livewire\Dialer\DialerInterface;
use App\Models\CallSession;
use App\Models\CallTranscript;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('polls for transcript updates and renders saved entries', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => 'active']);
    $contact = Contact::factory()->for($campaign)->create();

    $callSession = CallSession::factory()
        ->for($contact, 'contact')
        ->for($campaign, 'campaign')
        ->for($user, 'vaUser')
        ->create([
            'status' => 'in_progress',
            'twilio_call_sid' => null,
        ]);

    $callSession->transcripts()->create([
        'speaker' => 'prospect',
        'text' => 'Hello there',
        'timestamp' => 1.5,
    ]);

    actingAs($user);

    Livewire::test(DialerInterface::class, ['embedded' => true])
        ->assertSee('Hello there', escape: false)
        ->assertSeeHtml('wire:poll.3s="reloadTranscripts"');
});

it('automatically seeds mock transcripts when requested', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'local');

    try {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['status' => 'active']);
        $contact = Contact::factory()->for($campaign)->create();

        actingAs($user);

        Event::fake([CallTranscriptUpdated::class]);

        Livewire::test(DialerInterface::class, [
            'embedded' => true,
            'initialContactId' => $contact->id,
            'shouldMock' => true,
        ])
            ->assertSee('Mock call connected.');

        Event::assertDispatchedTimes(CallTranscriptUpdated::class, 5);

        expect(CallTranscript::count())->toBe(5);
    } finally {
        app()->instance('env', $originalEnv);
    }
});

it('pushes mock transcripts through the Livewire action', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'local');

    try {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['status' => 'active']);
        $contact = Contact::factory()->for($campaign)->create();

        $callSession = CallSession::factory()
            ->for($contact, 'contact')
            ->for($campaign, 'campaign')
            ->for($user, 'vaUser')
            ->create([
                'status' => 'in_progress',
            ]);

        actingAs($user);

        Event::fake([CallTranscriptUpdated::class]);

        Livewire::test(DialerInterface::class, ['embedded' => true])
            ->call('sendMockTranscripts')
            ->assertSee('Mock call connected.');

        Event::assertDispatchedTimes(CallTranscriptUpdated::class, 5);

        expect(
            CallTranscript::where('call_session_id', $callSession->id)->count()
        )->toBe(5);
    } finally {
        app()->instance('env', $originalEnv);
    }
});
