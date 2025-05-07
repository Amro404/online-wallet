<?php

namespace Src\Domain\Banking\Repositories;

use App\Models\RawTransactionWebhook as RawTransactionWebhookModel;
use Illuminate\Support\LazyCollection;
use Src\Domain\Banking\DataTransferObjects\RawTransactionWebhook;
use Src\Domain\Banking\Enums\RawTransactionWebhookStatus;

interface RawTransactionWebhookRepositoryInterface
{
    public function create(RawTransactionWebhook $transactionWebhook): RawTransactionWebhookModel;
    public function getPendingWebhooks(): LazyCollection;
    public function markWebhooksAs(array $transactionIds, RawTransactionWebhookStatus $status): void;

}
