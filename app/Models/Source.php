<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'allows_cold_outreach_api',
        'operator_warning',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allows_cold_outreach_api' => 'boolean',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}