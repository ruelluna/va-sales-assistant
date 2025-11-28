<?php

namespace Database\Factories;

use App\Models\CallSession;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallSessionFactory extends Factory
{
    protected $model = CallSession::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'campaign_id' => Campaign::factory(),
            'va_user_id' => User::factory(),
            'twilio_call_sid' => 'CA' . fake()->numerify('##########'),
            'direction' => 'outbound',
            'status' => 'initiated',
            'started_at' => null,
            'ended_at' => null,
            'duration_seconds' => null,
            'recording_url' => null,
            'outcome' => null,
            'outcome_confidence' => null,
            'summary' => null,
            'next_action' => null,
            'next_action_due_at' => null,
            'ai_state' => [],
            'real_time_tags' => [],
            'ai_raw_data' => [],
        ];
    }
}
