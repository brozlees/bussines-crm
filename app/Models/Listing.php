<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    public const DIRECTION_OWN = 'own';
    public const DIRECTION_FOREIGN = 'foreign';

    protected $fillable = [
        'source_id',
        'external_id',
        'direction',
        'title',
        'description',
        'category',
        'city',
        'price',
        'url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'raw_payload',
        'published_at',
        'fetched_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'raw_payload' => 'array',
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function scopeOwn($query)
    {
        return $query->where('direction', self::DIRECTION_OWN);
    }

    public function scopeForeign($query)
    {
        return $query->where('direction', self::DIRECTION_FOREIGN);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSource($query, int $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    public function isOwn(): bool
    {
        return $this->direction === self::DIRECTION_OWN;
    }

    public function isForeign(): bool
    {
        return $this->direction === self::DIRECTION_FOREIGN;
    }
}