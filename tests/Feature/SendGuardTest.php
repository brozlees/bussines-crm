<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Consent;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Source;
use App\Services\AntiSpam\SendGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendGuardTest extends TestCase
{
    use RefreshDatabase;

    private Source $avito;
    private Source $site;
    private SendGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->avito = Source::create([
            'code' => 'avito',
            'name' => 'Avito',
            'allows_cold_outreach_api' => false,
            'operator_warning' => 'Ручная отправка',
            'settings' => ['rate_limits' => ['messages_per_hour_per_account' => 20]],
            'is_active' => true,
        ]);

        $this->site = Source::create([
            'code' => 'site',
            'name' => 'Сайт',
            'allows_cold_outreach_api' => true,
            'settings' => [],
            'is_active' => true,
        ]);

        $this->guard = new SendGuard();
    }

    private function makeListing(Source $source): Listing
    {
        return Listing::create([
            'source_id' => $source->id,
            'external_id' => 'ext-' . uniqid(),
            'direction' => 'foreign',
            'title' => 'Тестовое объявление',
            'is_active' => true,
        ]);
    }

    private function makeClient(bool $withEmailConsent = false): Client
    {
        $client = Client::create([
            'source_type' => 'site',
            'external_id' => 'client-' . uniqid(),
            'name' => 'Тестовый клиент',
            'email' => 'test' . uniqid() . '@example.com',
        ]);

        if ($withEmailConsent) {
            Consent::create([
                'client_id' => $client->id,
                'channel' => 'email',
                'consent_text' => 'Согласие на email',
                'granted_at' => now(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'source' => 'site',
            ]);
            $client->recalculateMarketingAllowed();
        }

        return $client;
    }

    public function test_avito_manual_lead_is_blocked(): void
    {
        $lead = Lead::create([
            'type' => Lead::TYPE_OUTBOUND_COLD,
            'listing_id' => $this->makeListing($this->avito)->id,
            'client_id' => $this->makeClient(true)->id,
            'delivery_mode' => Lead::DELIVERY_MANUAL,
            'channel' => 'avito',
            'status' => Lead::STATUS_DRAFT,
        ]);

        $result = $this->guard->check($lead, 'avito');

        $this->assertTrue($result->isBlocked());
        $this->assertSame('manual_required', $result->reasonCode);
    }

    public function test_email_without_consent_is_blocked(): void
    {
        $lead = Lead::create([
            'type' => Lead::TYPE_OUTBOUND_COLD,
            'listing_id' => $this->makeListing($this->site)->id,
            'client_id' => $this->makeClient(false)->id,
            'delivery_mode' => Lead::DELIVERY_EMAIL,
            'channel' => 'email',
            'status' => Lead::STATUS_DRAFT,
        ]);

        $result = $this->guard->check($lead, 'email');

        $this->assertTrue($result->isBlocked());
        $this->assertSame('no_consent', $result->reasonCode);
    }

    public function test_email_with_consent_is_allowed(): void
    {
        $lead = Lead::create([
            'type' => Lead::TYPE_OUTBOUND_COLD,
            'listing_id' => $this->makeListing($this->site)->id,
            'client_id' => $this->makeClient(true)->id,
            'delivery_mode' => Lead::DELIVERY_EMAIL,
            'channel' => 'email',
            'status' => Lead::STATUS_DRAFT,
        ]);

        $result = $this->guard->check($lead, 'email');

        $this->assertTrue($result->allowed);
    }
}