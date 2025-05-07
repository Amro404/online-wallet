<?php

namespace Src\Domain\Wallet\Services;

use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Models\Wallet;

class WalletDomainService
{
    public function __construct(protected WalletRepositoryInterface $walletRepository) {}


    public function updateBalance(WalletHolderInterface $holder, float|int $amount): void
    {
        $this->walletRepository->updateBalance($holder, $amount);
    }

    public function createWallet(WalletHolderInterface $holder, ?string $currency = null): void
    {
        $this->walletRepository->createWallet($holder, $currency);
    }

    public function getWallet(WalletHolderInterface $holder): ?Wallet
    {
        return $this->walletRepository->getWallet($holder);
    }
}
