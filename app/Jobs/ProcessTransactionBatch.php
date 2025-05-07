<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\Actions\ProcessBankTransactionsAction;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTransactionBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 180];

    public function __construct(
        public array $transactions
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ProcessBankTransactionsAction $processAction): void
    {
        try {
            $processAction->execute($this->transactions);
        } catch (\Exception $exception) {
            Log::error("Failed to process transaction webhook batch: " . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'payload' => $this->transactions,
            ]);

            throw $exception;
        }
    }
}
