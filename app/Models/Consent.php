<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'channel',
        'consent_text',
        'document_version',
        'granted_at',
        'revoked_at',
        'ip_address',
        'user_agent',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function revoke(): void
    {
        if ($this->revoked_at !== null) {
            return;
        }

        $this->revoked_at = now();
        $this->save();

        $this->client?->recalculateMarketingAllowed();
    }
}