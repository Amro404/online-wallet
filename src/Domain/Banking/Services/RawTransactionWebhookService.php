<?php

namespace Src\Domain\Banking\Services;

use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\DataTransferObjects\RawTransactionWebhook;
use Src\Domain\Banking\Repositories\RawTransactionWebhookRepositoryInterface;

class RawTransactionWebhookService
{
    public function __construct(protected RawTransactionWebhookRepositoryInterface $repository) {}

    public function handle(int $clientId, BankWebhookPayload $bankWebhookPayload): void
    {
        try {
            $rawTransactionWebhook = RawTransactionWebhook::create(
                $clientId,
                $bankWebhookPayload->getContent(),
                $bankWebhookPayload->getBankIdentifier(),
                $bankWebhookPayload->getHeaders(),
            );

            $this->repository->create($rawTransactionWebhook);

        } catch (\Exception $exception) {
            Log::error('Failed to log bank webhook transactions', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'client_id' => $clientId,
                'bank' => $bankWebhookPayload->getBankIdentifier(),
            ]);
        }
    }

}
