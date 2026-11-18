<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $table = 'communication_log';

    // Направление
    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';

    // Статусы
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'client_id',
        'lead_id',
        'conversation_id',
        'channel',
        'direction',
        'content',
        'status',
        'error_message',
        'sent_by_user_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeSince($query, \DateTimeInterface $since)
    {
        return $query->where('created_at', '>=', $since);
    }
}