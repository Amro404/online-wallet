<?php

namespace Tests\Unit\Domain\Banking\Services;

use App\Models\WebhookIngestionSetting;
use Tests\TestCase;
use Mockery;
use Src\Domain\Banking\Services\WebhookIngestionSettingService;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Repositories\WebhookIngestionSettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class WebhookIngestionSettingServiceTest extends TestCase
{
    private WebhookIngestionSettingRepositoryInterface $repository;
    private WebhookIngestionSettingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(WebhookIngestionSettingRepositoryInterface::class);
        $this->service = new WebhookIngestionSettingService($this->repository);

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function test_checks_if_ingestion_is_paused(): void
    {
        $clientId = 1;
        $bank = BankType::FOODICS;

        $setting = new WebhookIngestionSetting([
            'client_id' => $clientId,
            'bank_name' => $bank->value,
            'paused' => true
        ]);

        $this->repository->shouldReceive('getByClientAndBankType')
            ->with($clientId, $bank)
            ->andReturn($setting);

        $result = $this->service->webhookIngestionPaused($clientId, $bank);

        $this->assertTrue($result);
    }

    public function test_uses_cache_for_paused_check(): void
    {
        $clientId = 1;
        $bank = BankType::FOODICS;
        $cacheKey = "webhook_ingestion_paused_{$clientId}_{$bank->value}";

        $setting = new WebhookIngestionSetting([
            'client_id' => $clientId,
            'bank_name' => $bank->value,
            'paused' => true
        ]);

        $this->repository->shouldReceive('getByClientAndBankType')
            ->once()
            ->with($clientId, $bank)
            ->andReturn($setting);

        $this->assertFalse(Cache::has($cacheKey));

        $firstResult = $this->service->webhookIngestionPaused($clientId, $bank);

        $this->assertTrue($firstResult);
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_returns_false_when_no_pause_setting_exists(): void
    {
        $clientId = 1;
        $bank = BankType::FOODICS;

        $this->repository->shouldReceive('getByClientAndBankType')
            ->with($clientId, $bank)
            ->andReturn(null);

        $result = $this->service->webhookIngestionPaused($clientId, $bank);

        $this->assertFalse($result);
    }
}
