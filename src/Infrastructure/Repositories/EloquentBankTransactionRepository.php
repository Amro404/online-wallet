<?php

namespace Src\Infrastructure\Repositories;

use App\Models\BankTransaction;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Repositories\BankTransactionRepositoryInterface;

class EloquentBankTransactionRepository implements BankTransactionRepositoryInterface
{
    public function findExistingReferences(array $references, BankType $bankType): array
    {
        return BankTransaction::query()
            ->whereIn('reference', $references)
            ->where('bank_name', $bankType->value)
            ->pluck('reference')
            ->toArray();
    }

    public function bulkInsert(array $transactions): void
    {
        $dbTransactions = [];
        foreach ($transactions as $transaction) {
            $dbTransactions[] = [
                'id' => $transaction->getId(),
                'reference' => $transaction->getReference(),
                'amount' => $transaction->getAmount(),
                'currency' => $transaction->getCurrency(),
                'bank_name' => $transaction->getBank()->value,
                'date' => $transaction->getDate(),
                'meta' => json_encode($transaction->getMeta()),
                'client_id' => $transaction->getClientId(),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        BankTransaction::query()->insert($dbTransactions);
    }

}
