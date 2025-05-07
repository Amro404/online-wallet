<?php

namespace Src\Domain\Banking\Repositories;

use Src\Domain\Banking\Enums\BankType;

interface BankTransactionRepositoryInterface
{
    public function findExistingReferences(array $references, BankType $bankType): array;
    public function bulkInsert(array $transactions): void;
}
