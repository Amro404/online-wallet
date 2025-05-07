<?php

namespace Src\Domain\Wallet\Services;

use App\Models\Wallet;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;

class PrepareService
{
    public function __construct(protected ConsistencyService $consistencyService) {}

    public function deposit(
        Wallet                  $wallet,
        float                   $amount,
        ?array                  $meta,
        WalletTransactionStatus $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransaction
    {
        $this->consistencyService->checkPositive($amount);

        return WalletTransaction::create(
            clientId: $wallet->client_id,
            walletId: $wallet->id,
            type: WalletTransactionType::DEPOSIT,
            amount: $amount,
            status: $status,
            meta: $meta,
        );

    }

    public function withdraw(
        Wallet                  $wallet,
        float                   $amount,
        ?array                  $meta,
        WalletTransactionStatus $status = WalletTransactionStatus::SUCCESSFUL,

    ): WalletTransaction
    {
        $this->consistencyService->checkPositive($amount);

        return WalletTransaction::create(
            clientId: $wallet->client_id,
            walletId: $wallet->id,
            type: WalletTransactionType::WITHDRAW,
            amount: $amount * -1,
            status: $status,
            meta: $meta,
        );
    }
}
