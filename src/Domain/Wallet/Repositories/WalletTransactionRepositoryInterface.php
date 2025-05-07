<?php

namespace Src\Domain\Wallet\Repositories;

use App\Models\WalletTransaction as WalletTransactionModel;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;

interface WalletTransactionRepositoryInterface
{
    public function create(WalletTransaction $walletTransaction): WalletTransactionModel;
}
