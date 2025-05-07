<?php

namespace Src\Domain\Banking\Services;

use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Repositories\WebhookIngestionSettingRepositoryInterface;

class WebhookIngestionSettingService
{
    public function __construct(protected WebHookIngestionSettingRepositoryInterface $repository) {}

    public function webhookIngestionPaused(int $clientId, BankType $bank): bool
    {
        $cacheKey = $this->getPausedCacheKey($clientId, $bank);

        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($clientId, $bank) {
            $setting = $this->repository->getByClientAndBankType($clientId, $bank);

            return $setting->paused ?? false;
        });

    }

    public function pause(int $clientId, BankType $bank, ?string $reason = null): void
    {
        $this->repository->pause($clientId, $bank, $reason);

        cache()->forget($this->getPausedCacheKey($clientId, $bank));
    }

    public function resume(int $clientId, BankType $bank): void
    {
        $this->repository->resume($clientId, $bank);

        cache()->forget($this->getPausedCacheKey($clientId, $bank));
    }


    protected function getPausedCacheKey(int $clientId, BankType $bank): string
    {
        return "webhook_ingestion_paused_{$clientId}_{$bank->value}";
    }
}
