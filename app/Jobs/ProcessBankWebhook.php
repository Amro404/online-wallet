<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\Services\BankWebhookHandlerService;
use Src\Domain\Banking\Services\RawTransactionWebhookService;
use Src\Domain\Banking\Services\WebhookIngestionSettingService;
use Src\Domain\Client\Services\ClientService;

class ProcessBankWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 180];

    public function __construct(
        private readonly BankWebhookPayload $webhookPayload
    ) {}

    public function handle(
        ClientService $clientService,
        BankWebhookHandlerService $bankWebhookHandler,
        WebhookIngestionSettingService $webhookIngestionService,
        RawTransactionWebhookService $rawTransactionWebhookService
    ): void
    {
        try {
            $merchantId = $this->webhookPayload->getMerchantId();
            $bank = $this->webhookPayload->getBankIdentifier();
            // I assumed there is a provider/bank's merchant id or a key in the webhook request header to resolve the client
            $client = $clientService->getByMerchantId($merchantId);

            if ($client == null) {
                Log::warning("Client not found for merchant ID: $merchantId");
                return;
            }

            if ($webhookIngestionService->webhookIngestionPaused($client->id, $bank)) {
                // Save for later processing
                $rawTransactionWebhookService->handle($client->id, $this->webhookPayload);
            } else {
                // Proceed with processing
                $bankWebhookHandler->handle($client->id, $this->webhookPayload);
            }
        } catch (\Exception $exception) {

            Log::error("Failed to process webhook: " . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'payload' => (array) $this->webhookPayload,
            ]);

            throw $exception;
        }
    }
}
