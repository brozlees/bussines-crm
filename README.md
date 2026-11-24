# Business Service CRM

CRM-система для управления бизнесом услуг с интеграцией внешних досок объявлений (Avito и др.) и ИИ-поиском потенциальных клиентов.

## Что это

Единая панель, в которой можно:

- Видеть свои объявления и входящие обращения с разных площадок в одном месте.
- Получать уведомления о новых сообщениях от клиентов.
- Вести переписку с клиентами внутри CRM.
- Находить потенциальных клиентов по чужим объявлениям на площадках (ИИ-анализ).
- Генерировать персонализированные предложения и отправлять их — с соблюдением правил площадок и антиспам-ограничений.

## Ключевой принцип: антиспам и правила площадок

Система построена вокруг защиты от спама и блокировок:

- **Для Avito холодная рассылка незнакомцам автоматически невозможна** — правила площадки это запрещают, API не позволяет писать первым. CRM готовит черновик, оператор отправляет вручную и отмечает лид как «отправлено».
- **Перед каждой отправкой** проверяется: согласие клиента (для email/SMS/telegram), частотные лимиты площадки, кулдаун по клиенту.
- **Ручная отправка по умолчанию**, автоматическая — только как явное исключение для площадок, которые это разрешают.

## Стек

- **Backend:** PHP 8.4, Laravel 12
- **База данных:** PostgreSQL 16
- **Кэш и очереди:** Redis
- **Frontend:** Blade, Tailwind CSS, Alpine.js (через Laravel Breeze)
- **Сборка ассетов:** Vite
- **Контейнеризация:** Docker Compose
- **Очереди:** Laravel Horizon + Supervisor

## Требования

- Docker Desktop (Windows/macOS) или Docker + Docker Compose (Linux)
- Git

Node.js, PHP и Composer на хосте **не нужны** — всё выполняется внутри контейнеров.

## Установка

### 1. Клонировать репозиторий

```bash
git clone <URL-вашего-репозитория> bissnes-service.local
cd bissnes-service.local
```

### 2. Создать `.env` из примера

```bash
cp .env.example .env
```

Ключевые переменные (уже настроены под Docker):

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=laravel_db
DB_USERNAME=root
DB_PASSWORD=password

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

### 3. Запустить контейнеры

```bash
docker-compose up -d --build
```

Первая сборка занимает 10–20 минут (сборка PHP-образа с расширениями).

### 4. Установить зависимости внутри контейнера

```bash
docker-compose exec -u www-data app composer install
docker-compose exec -u www-data app php artisan key:generate
```

### 5. Применить миграции и заполнить базовыми данными

```bash
docker-compose exec -u www-data app php artisan migrate
docker-compose exec -u www-data app php artisan db:seed --class=SourceSeeder
docker-compose exec -u www-data app php artisan db:seed --class=UserSeeder
```

### 6. Открыть в браузере

- Сайт: http://localhost
- Админка: http://localhost/leads

**Учётные данные по умолчанию (из `UserSeeder`):**
- Email: `admin@bissnes.local`
- Пароль: `password`

> Смените пароль перед публикацией в открытый интернет.

## Архитектура БД

| Таблица | Назначение |
|---|---|
| `sources` | Реестр площадок (Avito, сайт и т.д.) с настройками и лимитами |
| `clients` | Клиенты/контакты с привязкой к источнику |
| `consents` | Согласия на маркетинговые сообщения (по каналам), история отзывов |
| `listings` | Объявления: свои (`direction=own`) и чужие (`direction=foreign`) |
| `leads` | Найденные возможности: `inbound` (клиент написал) и `outbound_cold` (ИИ нашёл) |
| `conversations` | Диалоги с клиентами по площадкам |
| `messages` | Сообщения внутри диалогов |
| `communication_log` | Журнал всех отправок — для аудита и частотных лимитов |

## Антиспам-ядро (`app/Services/AntiSpam`)

- `SendGuard::check(Lead $lead, string $channel): CheckResult` — единая точка проверки перед отправкой.
- Проверяет по порядку: ручной режим → активность площадки → согласие клиента → кулдаун → лимиты площадки → лимиты диалога.
- Настройки в `config/anti_spam.php`.
- Тесты: `tests/Feature/SendGuardTest.php`.

## Разработка

### Полезные команды

```bash
# Логи приложения
docker-compose exec app tail -f storage/logs/laravel.log

# Очереди (если нужно запустить вручную, а не через Supervisor)
docker-compose exec -u www-data app php artisan queue:work

# Horizon (мониторинг очередей)
docker-compose exec -u www-data app php artisan horizon

# Тесты
docker-compose exec -u www-data app php artisan test

# Очистить кэши
docker-compose exec -u www-data app php artisan optimize:clear

# Пересоздать базу с нуля
docker-compose exec -u www-data app php artisan migrate:fresh --seed

# Попасть в контейнер
docker-compose exec app bash
```

### Тесты

```bash
docker-compose exec -u www-data app php artisan test
```

Используется PHPUnit + `RefreshDatabase`. Каждый тест откатывает изменения в БД после себя.

## Структура проекта

```
app/
├── Http/Controllers/       # Контроллеры (в т.ч. LeadReviewController)
├── Models/                 # Eloquent-модели
└── Services/AntiSpam/      # Ядро антиспам-логики

config/
├── anti_spam.php           # Настройки антиспама (кулдаун, каналы)

database/
├── migrations/             # Схема БД
└── seeders/                # Сидеры (SourceSeeder, UserSeeder)

resources/views/
├── leads/                  # Раздел «На проверку»
└── ...

tests/Feature/
└── SendGuardTest.php       # Тесты антиспам-логики
```

## Что дальше (Roadmap)

- [ ] Интеграция с Avito API (получение своих объявлений, чаты через webhook)
- [ ] ИИ-поиск: парсинг чужих объявлений, скоринг релевантности, генерация предложений
- [ ] Автоматическая отправка через email/SMS при наличии согласия
- [ ] Обработка отписок в один клик (обязательно по закону)
- [ ] Настройка SPF/DKIM/DMARC для домена рассылки
- [ ] Роли и права операторов
- [ ] Уведомления о новых сообщениях (email/push)
- [ ] Дашборд со статистикой

## Лицензия

Проприетарный проект. Все права защищены.