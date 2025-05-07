<?php

namespace Src\Domain\Wallet\Contracts;

use App\Models\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;

interface WalletInterface
{
    public function deposit(
        WalletHolderInterface    $holder,
        float|int                $amount,
        ?array                   $meta = [],
        WalletTransactionStatus  $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransaction;
    public function withdraw(
        WalletHolderInterface    $holder,
        float|int                $amount,
        ?array                   $meta = [],
        WalletTransactionStatus  $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransaction;
    public function canWithdraw(WalletHolderInterface $holder, float $amount): bool;
    public function balance(WalletHolderInterface $holder): float;
}
