<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Campaign',
            'product_name' => fake()->words(2, true).' Product',
            'script' => fake()->paragraphs(3, true),
            'ai_prompt_context' => fake()->paragraph(),
            'success_definition' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
