<?php

namespace Src\Domain\Wallet\Services;

use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Exceptions\AmountInvalidBaseException;
use Src\Domain\Wallet\Exceptions\BalanceIsEmptyBaseException;
use Src\Domain\Wallet\Exceptions\InsufficientFundsBaseException;

class ConsistencyService
{
    public function __construct(protected WalletDomainService $walletDomainService) {}

    public function checkPositive(float|int $amount): void
    {
        if ($amount <= 0) {
            throw new AmountInvalidBaseException();
        }
    }

    public function checkPotential(WalletHolderInterface $wallet, float|int $amount): void
    {
        $wallet = $this->walletDomainService->getWallet($wallet);

        $balance = $wallet->balance ?? 0;

        if ($amount !== 0 && $balance == 0) {
            throw new BalanceIsEmptyBaseException();
        }

        if (!$this->canWithdraw($balance, $amount)) {
            throw new InsufficientFundsBaseException();
        }
    }

    public function canWithdraw(float|int $balance, float|int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return ($balance - $amount) >= 0;
    }
}
