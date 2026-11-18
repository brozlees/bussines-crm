<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    // Тип диалога
    public const TYPE_INBOUND = 'inbound';   // клиент написал первым
    public const TYPE_OUTBOUND = 'outbound'; // мы инициировали

    // Статусы
    public const STATUS_OPEN = 'open';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'source_id',
        'external_chat_id',
        'client_id',
        'listing_id',
        'type',
        'last_message_at',
        'last_message_preview',
        'unread_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'unread_count' => 'integer',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInbound($query)
    {
        return $query->where('type', self::TYPE_INBOUND);
    }

    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function canReplyViaApi(): bool
    {
        if ($this->type === self::TYPE_INBOUND) {
            return true;
        }

        return $this->source?->allows_cold_outreach_api === true;
    }

    public function markAsRead(): void
    {
        $this->unread_count = 0;
        $this->saveQuietly();
    }
}