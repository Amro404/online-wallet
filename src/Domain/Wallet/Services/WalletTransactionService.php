<?php

namespace Src\Domain\Wallet\Services;

use App\Models\WalletTransaction as WalletTransactionModel;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;
use Src\Domain\Wallet\Events\WalletTransactionCreated;
use Src\Domain\Wallet\Repositories\WalletTransactionRepositoryInterface;

class WalletTransactionService
{
    public function __construct(
        protected WalletTransactionRepositoryInterface $walletTransactionRepository,
        protected WalletDomainService $walletDomainService,
        protected PrepareService $prepareService,
        protected ConsistencyService $walletService
    ) {}

    public function initiateTransaction(
        WalletHolderInterface $walletHolder,
        WalletTransactionType $type,
        float|int $amount,
        ?array $meta,
        WalletTransactionStatus $status = WalletTransactionStatus::SUCCESSFUL,
    ): WalletTransactionModel
    {
        if (!in_array($type, [WalletTransactionType::DEPOSIT, WalletTransactionType::WITHDRAW])) {
            throw new \InvalidArgumentException("Invalid transaction type: $type->value");
        }

        $wallet = $this->walletDomainService->getWallet($walletHolder);

        $dto = $type === WalletTransactionType::DEPOSIT
            ? $this->prepareService->deposit($wallet, $amount, $meta, $status)
            : $this->prepareService->withdraw($wallet, $amount, $meta, $status);

        return $this->confirmTransaction($walletHolder, $dto);
    }

    public function confirmTransaction(WalletHolderInterface $walletHolder, WalletTransaction $transactionDto): WalletTransactionModel
    {
        $transaction = $this->walletTransactionRepository->create($transactionDto);

        $this->walletDomainService->updateBalance($walletHolder, $transactionDto->getAmount());

        event(new WalletTransactionCreated($transaction));

        return $transaction;
    }
}


