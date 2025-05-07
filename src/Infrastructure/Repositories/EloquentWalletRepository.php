<?php

namespace Src\Infrastructure\Repositories;

use App\Models\Wallet;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Exceptions\WalletNotFoundException;
use Src\Domain\Wallet\Repositories\WalletRepositoryInterface;

class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function getWallet(WalletHolderInterface $holder): ?Wallet
    {
        $wallet = Wallet::query()
            ->where('client_id', $holder->getId())
            ->first();

        if ($wallet == null) {
            throw new WalletNotFoundException($holder->getId());
        }

        return $wallet;
    }

    public function updateBalance(WalletHolderInterface $holder, float|int $amount): void
    {
        Wallet::query()
            ->where('client_id', $holder->getId())
            ->lockForUpdate()
            ->increment('balance', $amount);
    }

    public function createWallet(WalletHolderInterface $holder, ?string $currency = null): Wallet
    {
        return Wallet::query()->create([
            'client_id' => $holder->getId(),
            'balance' => 0,
            'currency' => $currency,
        ]);
    }

    public function balance(WalletHolderInterface $holder): ?float
    {
        return Wallet::query()
            ->select('balance')
            ->where('client_id', $holder->getId())
            ->value('balance');
    }
}
