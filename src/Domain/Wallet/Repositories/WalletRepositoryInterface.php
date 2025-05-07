<?php

namespace Src\Domain\Wallet\Repositories;

use App\Models\Wallet;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;

interface WalletRepositoryInterface
{
    public function getWallet(WalletHolderInterface $holder): ?Wallet;
    public function updateBalance(WalletHolderInterface $holder, float|int $amount): void;
    public function createWallet(WalletHolderInterface $holder, ?string $currency = null): Wallet;
    public function balance(WalletHolderInterface $holder): ?float;
}
