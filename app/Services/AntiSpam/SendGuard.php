<?php

namespace App\Services\AntiSpam;

use App\Models\CommunicationLog;
use App\Models\Lead;
use App\Models\Message;
use Illuminate\Support\Carbon;

class SendGuard
{
    
    public function check(Lead $lead, string $channel): CheckResult
    {
        if ($lead->delivery_mode === Lead::DELIVERY_MANUAL) {
            return CheckResult::blocked(
                'manual_required',
                'Требуется ручная отправка оператором.',
                meta: ['platform_warning' => $lead->platform_warning],
            );
        }

        $source = $lead->listing?->source;
        if (! $source || ! $source->is_active) {
            return CheckResult::blocked('source_inactive', 'Площадка неактивна или не найдена.');
        }

        $client = $lead->client;
        if (! $client) {
            return CheckResult::blocked(
                'no_client',
                'Нет карточки клиента — невозможно проверить согласие.',
            );
        }

        $marketingChannels = config('anti_spam.marketing_channels', []);
        if (in_array($channel, $marketingChannels, true)) {
            if (! $client->hasActiveConsent($channel)) {
                return CheckResult::blocked(
                    'no_consent',
                    "Нет активного согласия на канал «{$channel}».",
                );
            }
        }

        $cooldownDays = (int) config('anti_spam.client_cooldown_days', 7);
        $maxInCooldown = (int) config('anti_spam.client_max_messages_per_cooldown', 1);

        if ($cooldownDays > 0) {
            $recentQuery = CommunicationLog::query()
                ->where('client_id', $client->id)
                ->where('direction', CommunicationLog::DIRECTION_OUTBOUND)
                ->where('created_at', '>=', now()->subDays($cooldownDays));

            if ($recentQuery->count() >= $maxInCooldown) {
                $lastSentAt = CommunicationLog::query()
                    ->where('client_id', $client->id)
                    ->where('direction', CommunicationLog::DIRECTION_OUTBOUND)
                    ->latest('created_at')
                    ->value('created_at');

                $retryAfter = null;
                if ($lastSentAt) {
                    $retryAt = Carbon::parse($lastSentAt)->addDays($cooldownDays);
                    $retryAfter = max(0, $retryAt->getTimestamp() - now()->getTimestamp());
                }

                return CheckResult::blocked(
                    'client_cooldown',
                    "Клиенту уже отправляли сообщение за последние {$cooldownDays} дн.",
                    retryAfterSeconds: $retryAfter,
                );
            }
        }

        $limits = $source->settings['rate_limits'] ?? [];

        if (! empty($limits['messages_per_hour_per_account'])) {
            $count = CommunicationLog::query()
                ->where('channel', $channel)
                ->where('direction', CommunicationLog::DIRECTION_OUTBOUND)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($count >= (int) $limits['messages_per_hour_per_account']) {
                return CheckResult::blocked(
                    'account_hour_limit',
                    "Превышен часовой лимит площадки ({$limits['messages_per_hour_per_account']}/час).",
                );
            }
        }

        if (! empty($limits['messages_per_day_per_account'])) {
            $count = CommunicationLog::query()
                ->where('channel', $channel)
                ->where('direction', CommunicationLog::DIRECTION_OUTBOUND)
                ->where('created_at', '>=', now()->subDay())
                ->count();

            if ($count >= (int) $limits['messages_per_day_per_account']) {
                return CheckResult::blocked(
                    'account_day_limit',
                    "Превышен дневной лимит площадки ({$limits['messages_per_day_per_account']}/сутки).",
                );
            }
        }

        if ($lead->conversation_id) {
            if (! empty($limits['messages_per_chat_per_hour'])) {
                $count = Message::query()
                    ->where('conversation_id', $lead->conversation_id)
                    ->where('direction', Message::DIRECTION_OUTBOUND)
                    ->where('created_at', '>=', now()->subHour())
                    ->count();

                if ($count >= (int) $limits['messages_per_chat_per_hour']) {
                    return CheckResult::blocked(
                        'chat_hour_limit',
                        'Превышен лимит сообщений в диалоге за час.',
                    );
                }
            }

            if (! empty($limits['messages_per_chat_per_day'])) {
                $count = Message::query()
                    ->where('conversation_id', $lead->conversation_id)
                    ->where('direction', Message::DIRECTION_OUTBOUND)
                    ->where('created_at', '>=', now()->subDay())
                    ->count();

                if ($count >= (int) $limits['messages_per_chat_per_day']) {
                    return CheckResult::blocked(
                        'chat_day_limit',
                        'Превышен лимит сообщений в диалоге за сутки.',
                    );
                }
            }
        }

        return CheckResult::allowed();
    }
}