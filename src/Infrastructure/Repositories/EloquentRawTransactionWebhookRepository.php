<?php

namespace Src\Infrastructure\Repositories;

use Src\Domain\Banking\DataTransferObjects\RawTransactionWebhook;
use Src\Domain\Banking\Enums\RawTransactionWebhookStatus;
use Src\Domain\Banking\Repositories\RawTransactionWebhookRepositoryInterface;
use App\Models\RawTransactionWebhook as RawTransactionWebhookModel;
use Illuminate\Support\LazyCollection;

class EloquentRawTransactionWebhookRepository implements RawTransactionWebhookRepositoryInterface
{
    public function create(RawTransactionWebhook $transactionWebhook): RawTransactionWebhookModel
    {
        return RawTransactionWebhookModel::query()->create([
            'client_id' => $transactionWebhook->getClientId(),
            'payload' => trim($transactionWebhook->getContent()),
            'headers' => json_encode($transactionWebhook->getHeaders()),
            'bank_name' => $transactionWebhook->getBankType()->value,
            'status' => $transactionWebhook->getStatus()->value,
            'received_at' => now()
        ]);
    }

    public function getPendingWebhooks(): LazyCollection
    {
        return RawTransactionWebhookModel::query()
            ->orderBy('id')
            ->where('status', RawTransactionWebhookStatus::PENDING->value)
            ->cursor();
    }

    public function markWebhooksAs(array $transactionIds, RawTransactionWebhookStatus $status): void
    {
        if (empty($transactionIds)) {
            return;
        }

        RawTransactionWebhookModel::query()
            ->whereIn('id', $transactionIds)
            ->update(['status' => $status->value]);
    }

}
