<?php

namespace Src\Domain\Banking\Actions;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\Repositories\BankTransactionRepositoryInterface;
use Src\Domain\Client\Services\ClientService;
use Src\Domain\Wallet\Services\WalletService;

class ProcessBankTransactionsAction
{
    public function __construct(
        protected BankTransactionRepositoryInterface $bankTransactionRepository,
        protected ClientService $clientService,
        protected WalletService $walletService
    ) {}

    public function execute(array $transactions): void
    {
        if (empty($transactions)) return;

        try {
            DB::beginTransaction();

            $transactions = $this->filterDuplicateTransactions($transactions);

            if (empty($transactions)) return;

            $this->bankTransactionRepository->bulkInsert($transactions);

            $client = $this->clientService->getById($transactions[0]->getClientId());

            $this->applyToWallet($transactions, $client);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            Log::error('Failed to process bank transactions ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'payload' => $transactions,
            ]);
        }

    }

    protected function filterDuplicateTransactions(array $transactions): array
    {
        $references = array_map(fn($t) => $t->getReference(), $transactions);
        $existingReferences = $this->bankTransactionRepository->findExistingReferences($references, $transactions[0]->getBank());
        // convert references to keys for constant time O(1) lookups
        $existingLookup = array_flip($existingReferences);
        $transactions = array_filter($transactions, fn($transaction) => !isset($existingLookup[$transaction->getReference()]));
        return array_values($transactions);
    }

    protected function applyToWallet(array $transactions, Client $client): void
    {
        // Because the steps in the operation are atomic and rely on each other, we can't combine the insert queries into one.
        foreach ($transactions as $transaction) {
            $this->walletService->deposit(
                $client,
                $transaction->getAmount(),
                [
                    // we can make a contract on these or make a polymorphic column
                    'source_id' => $transaction->getId(),
                    'source_type' => 'bank_transaction',
                ]
            );
        }
    }
}
