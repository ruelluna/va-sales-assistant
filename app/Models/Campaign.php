<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_id',
        'product_name',
        'script',
        'ai_prompt_context',
        'success_definition',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute($value)
    {
        // If product_id exists and product is loaded, return product name
        if ($this->product_id && $this->relationLoaded('product') && $this->product) {
            return $this->product->name;
        }
        // Otherwise return the stored value or fallback
        return $value ?? ($this->product_id && $this->product ? $this->product->name : 'N/A');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function callSessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }
}
