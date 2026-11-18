<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    // Направление
    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';

    // Тип содержимого
    public const CONTENT_TEXT = 'text';
    public const CONTENT_IMAGE = 'image';
    public const CONTENT_FILE = 'file';
    public const CONTENT_SYSTEM = 'system';

    // Статусы доставки
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_READ = 'read';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'conversation_id',
        'external_message_id',
        'direction',
        'content_type',
        'text',
        'attachments',
        'external_author_id',
        'status',
        'error_message',
        'external_created_at',
        'lead_id',
        'sent_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'external_created_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    public function markAsRead(): void
    {
        if ($this->status === self::STATUS_READ) {
            return;
        }

        $this->status = self::STATUS_READ;
        $this->saveQuietly();
    }
}