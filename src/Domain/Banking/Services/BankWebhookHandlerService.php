<?php

namespace Src\Domain\Banking\Services;

use App\Jobs\ProcessTransactionBatch;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Factories\BankWebhookParserFactory;

class BankWebhookHandlerService
{
    private const PROCESS_PATCH_SIZE = 100;

    public function __construct(protected BankWebhookParserFactory $parserFactory,) {}

    public function handle(int $clientId, BankWebhookPayload $bankWebhookPayload): void
    {
        try {
            $parser = $this->parserFactory->fromBank($bankWebhookPayload->getBankIdentifier());
            $transactions = $parser->parse($bankWebhookPayload);

            if (empty($transactions)) return;

            // Assign client_id to each transaction DTO
            foreach ($transactions as $transaction) {
                $transaction->setClientId($clientId);
            }

            foreach (array_chunk($transactions, self::PROCESS_PATCH_SIZE) as $chunk) {
                ProcessTransactionBatch::dispatch($chunk);
            }

        } catch (\Throwable $exception) {
            Log::error('Failed to handle bank webhook', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'client_id' => $clientId,
                'bank' => $bankWebhookPayload->getBankIdentifier()->value,
            ]);
        }

    }
}
