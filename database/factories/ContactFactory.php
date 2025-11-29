<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->optional()->company(),
            'tags' => [],
            'timezone' => fake()->timezone(),
        ];
    }
}
