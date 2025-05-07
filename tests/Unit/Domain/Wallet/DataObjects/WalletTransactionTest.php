<?php

namespace Tests\Unit\Domain\Wallet\DataObjects;

use Tests\TestCase;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;

class WalletTransactionTest extends TestCase
{

    private function createTransaction(): WalletTransaction
    {
        return WalletTransaction::create(
            clientId: 1,
            walletId: 100,
            type: WalletTransactionType::DEPOSIT,
            amount: 100.50,
            status: WalletTransactionStatus::PENDING,
            meta: ['note' => 'Payment']
        );
    }

    public function test_it_creates_with_immutable_properties(): void
    {
        $transaction = $this->createTransaction();

        $this->assertEquals(1, $transaction->getClientId());
        $this->assertEquals(100, $transaction->getWalletId());
        $this->assertEquals(WalletTransactionType::DEPOSIT->value, $transaction->getType());
        $this->assertEquals(100.50, $transaction->getAmount());
        $this->assertEquals(WalletTransactionStatus::PENDING->value, $transaction->getStatus());
        $this->assertEquals(['note' => 'Payment'], $transaction->getMeta());
    }

    public function test_it_handles_different_transaction_types(): void
    {
        $types = [
            WalletTransactionType::DEPOSIT,
            WalletTransactionType::WITHDRAW,
        ];

        foreach ($types as $type) {
            $transaction = WalletTransaction::create(
                clientId: 1,
                walletId: 100,
                type: $type,
                amount: 50.00,
                status: WalletTransactionStatus::PENDING,
                meta: []
            );

            $this->assertEquals($type->value, $transaction->getType());
        }
    }

    public function test_it_handles_different_status_types(): void
    {
        $statuses = [
            WalletTransactionStatus::PENDING,
            WalletTransactionStatus::SUCCESSFUL,
            WalletTransactionStatus::FAILED,
        ];

        foreach ($statuses as $status) {
            $transaction = WalletTransaction::create(
                clientId: 1,
                walletId: 100,
                type: WalletTransactionType::DEPOSIT,
                amount: 50.00,
                status: $status,
                meta: []
            );

            $this->assertEquals($status->value, $transaction->getStatus());
        }
    }

    public function test_it_initializes_with_empty_meta(): void
    {
        $transaction = WalletTransaction::create(
            clientId: 1,
            walletId: 100,
            type: WalletTransactionType::DEPOSIT,
            amount: 200.00,
            status: WalletTransactionStatus::SUCCESSFUL,
            meta: []
        );

        $this->assertEquals([], $transaction->getMeta());
    }

    public function test_it_initializes_with_null_meta(): void
    {
        $transaction = WalletTransaction::create(
            clientId: 1,
            walletId: 100,
            type: WalletTransactionType::DEPOSIT,
            amount: 200.00,
            status: WalletTransactionStatus::SUCCESSFUL,
            meta: null
        );

        $this->assertNull($transaction->getMeta());
    }

    public function test_it_handles_different_amount_values(): void
    {
        $amounts = [0.01, 100.00, 9999.99, 1000000.00];

        foreach ($amounts as $amount) {
            $transaction = WalletTransaction::create(
                clientId: 1,
                walletId: 100,
                type: WalletTransactionType::DEPOSIT,
                amount: $amount,
                status: WalletTransactionStatus::PENDING,
                meta: []
            );

            $this->assertEquals($amount, $transaction->getAmount());
        }
    }
}
