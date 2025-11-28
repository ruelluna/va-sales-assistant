<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'campaign_id',
        'va_user_id',
        'twilio_call_sid',
        'direction',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'recording_url',
        'outcome',
        'outcome_confidence',
        'summary',
        'next_action',
        'next_action_due_at',
        'ai_state',
        'real_time_tags',
        'ai_raw_data',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'next_action_due_at' => 'datetime',
            'ai_state' => 'array',
            'real_time_tags' => 'array',
            'ai_raw_data' => 'array',
            'outcome_confidence' => 'decimal:2',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function vaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'va_user_id');
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(CallTranscript::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['ringing', 'in_progress']);
    }
}
