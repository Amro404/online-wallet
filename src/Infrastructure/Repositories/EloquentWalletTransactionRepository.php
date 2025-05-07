<?php

namespace Src\Infrastructure\Repositories;

use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Repositories\WalletTransactionRepositoryInterface;
use App\Models\WalletTransaction as WalletTransactionModel;

class EloquentWalletTransactionRepository implements WalletTransactionRepositoryInterface
{

    public function create(WalletTransaction $walletTransaction): WalletTransactionModel
    {
        return WalletTransactionModel::query()
            ->create([
                'client_id' => $walletTransaction->getClientId(),
                'wallet_id' => $walletTransaction->getWalletId(),
                'amount' => $walletTransaction->getAmount(),
                'type' => $walletTransaction->getType(),
                'status' => $walletTransaction->getStatus(),
                'meta' => json_encode($walletTransaction->getMeta()),
            ]);
    }

}
