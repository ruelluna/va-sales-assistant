<?php

declare(strict_types=1);

use App\Livewire\Campaigns\CampaignDetail;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('shows mock call button on campaign contact list outside production', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'local');

    try {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['status' => 'active']);
        Contact::factory()->for($campaign)->create([
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        actingAs($user);

        Livewire::test(CampaignDetail::class, ['id' => $campaign->id])
            ->assertSee('Mock Call');
    } finally {
        app()->instance('env', $originalEnv);
    }
});

it('hides mock call button in production', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'production');

    try {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['status' => 'active']);
        Contact::factory()->for($campaign)->create();

        actingAs($user);

        Livewire::test(CampaignDetail::class, ['id' => $campaign->id])
            ->assertDontSee('Mock Call');
    } finally {
        app()->instance('env', $originalEnv);
    }
});

it('dispatches a mock call event with the correct payload', function () {
    $originalEnv = app()->environment();
    app()->instance('env', 'local');

    try {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['status' => 'active']);
        $contact = Contact::factory()->for($campaign)->create();

        actingAs($user);

        Livewire::test(CampaignDetail::class, ['id' => $campaign->id])
            ->call('mockCall', $contact->id)
            ->assertDispatched('openDialer', [
                'contactId' => $contact->id,
                'shouldMock' => true,
            ]);
    } finally {
        app()->instance('env', $originalEnv);
    }
});
