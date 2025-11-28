<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'features',
        'pricing_info',
        'ai_prompt_context',
        'common_objections',
        'recommended_responses',
        'cold_call_script_template',
        'success_definition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'common_objections' => 'array',
            'recommended_responses' => 'array',
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
