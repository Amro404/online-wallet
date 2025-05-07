<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Enums\RawTransactionWebhookStatus;
use Src\Domain\Banking\Repositories\RawTransactionWebhookRepositoryInterface;
use Src\Domain\Banking\Services\BankWebhookHandlerService;

class ProcessPendingWebhookTransactions implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [60, 120, 180];

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(
        RawTransactionWebhookRepositoryInterface $rawTransactionWebhookRepository,
        BankWebhookHandlerService $webhookHandler
    ): void
    {
        $pendingRaws = $rawTransactionWebhookRepository->getPendingWebhooks();
        $processedIds = [];

        foreach ($pendingRaws as $rawTransaction) {
            try {
                $payload = BankWebhookPayload::create(
                    bank: $rawTransaction->bank_name,
                    content: $rawTransaction->payload
                );

                $webhookHandler->handle($rawTransaction->client_id, $payload);
                $processedIds[] = $rawTransaction->id;

            } catch (\Exception $exception) {
                Log::warning('Failed to process raw webhook transaction', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if (!empty($processedIds)) {
            $rawTransactionWebhookRepository->markWebhooksAs($processedIds, RawTransactionWebhookStatus::PROCESSED);
        }

    }
}
