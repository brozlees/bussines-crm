<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    // Тип лида
    public const TYPE_INBOUND = 'inbound';
    public const TYPE_OUTBOUND_COLD = 'outbound_cold';

    // Режим доставки
    public const DELIVERY_MANUAL = 'manual_required';
    public const DELIVERY_API = 'api_allowed';
    public const DELIVERY_EMAIL = 'email';
    public const DELIVERY_SMS = 'sms';
    public const DELIVERY_TELEGRAM = 'telegram';

    // Статусы
    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT = 'sent';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'type',
        'listing_id',
        'client_id',
        'conversation_id',
        'relevance_score',
        'ai_reasoning',
        'proposal_text',
        'delivery_mode',
        'channel',
        'platform_warning',
        'status',
        'approved_at',
        'sent_at',
        'decided_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'relevance_score' => 'integer',
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function scopeInbound($query)
    {
        return $query->where('type', self::TYPE_INBOUND);
    }

    public function scopeOutboundCold($query)
    {
        return $query->where('type', self::TYPE_OUTBOUND_COLD);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeManualRequired($query)
    {
        return $query->where('delivery_mode', self::DELIVERY_MANUAL);
    }

    public function canBeSentAutomatically(): bool
    {
        if ($this->delivery_mode === self::DELIVERY_MANUAL) {
            return false;
        }

        if ($this->delivery_mode === self::DELIVERY_API) {
            return $this->listing?->source?->allows_cold_outreach_api === true;
        }

        return in_array($this->delivery_mode, [
            self::DELIVERY_EMAIL,
            self::DELIVERY_SMS,
            self::DELIVERY_TELEGRAM,
        ], true);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_APPROVED], true);
    }
}