<?php

namespace Src\Domain\Banking\Repositories;

use App\Models\WebhookIngestionSetting;
use Src\Domain\Banking\Enums\BankType;

interface WebhookIngestionSettingRepositoryInterface
{
    public function getByClientAndBankType(int $clientId, BankType $bank): ?WebhookIngestionSetting;
    public function pause(int $clientId, BankType $bank, ?string $reason): WebhookIngestionSetting;
    public function resume(int $clientId, BankType $bank): WebhookIngestionSetting;


}
