<?php

namespace Src\Domain\Wallet\Services;

use App\Models\WalletTransaction;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Contracts\WalletInterface;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;
use Src\Domain\Wallet\Repositories\WalletRepositoryInterface;

class WalletService implements WalletInterface
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected WalletTransactionService $walletTransactionService,
        protected ConsistencyService $consistencyService,
        protected LockService $lockService,

    ) {}

    public function deposit(
        WalletHolderInterface    $holder,
        float|int                $amount,
        ?array                   $meta = [],
        WalletTransactionStatus  $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransaction {
        return $this->lockService->block(
            $holder,

            fn() => $this->walletTransactionService
                ->initiateTransaction($holder, WalletTransactionType::DEPOSIT, $amount, $meta, $status)
        );

    }

    public function withdraw(
        WalletHolderInterface    $holder,
        float|int                $amount,
        ?array                   $meta = [],
        WalletTransactionStatus  $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransaction {
        return $this->lockService->block(
            $holder,
            function () use ($amount, $meta, $status, $holder) {

                $this->consistencyService->checkPotential($holder, $amount);

                return $this->walletTransactionService
                    ->initiateTransaction($holder, WalletTransactionType::WITHDRAW, $amount, $meta, $status);
            }
        );

    }

    public function canWithdraw(WalletHolderInterface $holder, float $amount): bool
    {
        $balance = $this->balance($holder) ?? 0;
        return $this->consistencyService->canWithdraw($balance, $amount);
    }

    public function balance(WalletHolderInterface $holder): float
    {
        return $this->walletRepository->balance($holder) ?? 0;
    }

}
