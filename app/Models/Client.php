<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'source_type',
        'external_id',
        'name',
        'email',
        'phone',
        'meta',
        'marketing_allowed',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'marketing_allowed' => 'boolean',
        ];
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }


    public function hasActiveConsent(string $channel): bool
    {
        return $this->consents()
            ->where('channel', $channel)
            ->whereNull('revoked_at')
            ->exists();
    }

    public function recalculateMarketingAllowed(): void
    {
        $this->marketing_allowed = $this->consents()
            ->whereNull('revoked_at')
            ->exists();

        $this->saveQuietly();
    }
}