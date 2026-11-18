<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'code' => 'avito',
                'name' => 'Avito',
                'allows_cold_outreach_api' => false,
                'operator_warning' => 'Avito: автоматическая отправка холодных сообщений запрещена правилами площадки. Массовая рассылка ведёт к перманентной блокировке без права восстановления. Скопируйте текст, отправьте вручную через интерфейс Avito и отметьте лид как «отправлено».',
                'settings' => [
                    'base_url' => 'https://api.avito.ru',
                    'webhook_supported' => true,
                    'rate_limits' => [
                        'messages_per_hour_per_account' => 20,
                        'messages_per_day_per_account' => 100,
                        'messages_per_chat_per_hour' => 3,
                        'messages_per_chat_per_day' => 10,
                    ],
                ],
                'is_active' => true,
            ],
            [
                'code' => 'site',
                'name' => 'Собственный сайт',
                'allows_cold_outreach_api' => true,
                'operator_warning' => null,
                'settings' => [
                    'webhook_supported' => false,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($sources as $data) {
            Source::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}