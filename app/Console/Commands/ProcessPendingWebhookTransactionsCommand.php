<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPendingWebhookTransactions;
use Illuminate\Console\Command;

class ProcessPendingWebhookTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-pending-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process webhook transactions that were deferred during paused ingestion';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ProcessPendingWebhookTransactions::dispatch();
    }
}
