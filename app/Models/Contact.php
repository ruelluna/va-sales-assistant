<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'company',
        'tags',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function callSessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class)->orderBy('created_at', 'desc');
    }

    public function latestNote()
    {
        return $this->hasOne(ContactNote::class)->latestOfMany();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
