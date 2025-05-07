<?php

namespace Src\Infrastructure\Repositories;

use App\Models\WebhookIngestionSetting;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Repositories\WebhookIngestionSettingRepositoryInterface;

class EloquentWebhookIngestionSettingRepository implements WebhookIngestionSettingRepositoryInterface
{
    public function getByClientAndBankType(int $clientId, BankType $bank): ?WebhookIngestionSetting
    {
        return WebhookIngestionSetting::query()
            ->where('client_id', $clientId)
            ->where('bank_name', $bank->value)
            ->first();
    }

    public function pause($clientId, BankType $bank, $reason = null): WebhookIngestionSetting
    {
        return WebhookIngestionSetting::query()
            ->updateOrCreate(
                ['client_id' => $clientId, 'bank_name' => $bank->value],
                [
                    'paused' => true,
                    'reason' => $reason,
                    'paused_at' => now(),
                    'resumed_at' => null
                ]
            );
    }

    public function resume($clientId, BankType $bank): WebhookIngestionSetting
    {
        return WebhookIngestionSetting::query()
            ->updateOrCreate(
                ['client_id' => $clientId, 'bank_name' => $bank->name],
                [
                    'paused' => false,
                    'reason' => null,
                    'resumed_at' => now(),
                    'paused_at' => null
                ]
            );
    }
}
