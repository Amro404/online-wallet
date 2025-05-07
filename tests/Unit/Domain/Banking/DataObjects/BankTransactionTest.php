<?php

namespace Tests\Unit\Domain\Banking\DataObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Banking\DataTransferObjects\BankTransaction;
use Src\Domain\Banking\Enums\BankType;

class BankTransactionTest extends TestCase
{
    private function createTransaction(): BankTransaction
    {
        return BankTransaction::create(
            date: '2023-01-01',
            amount: '100.50',
            currency: 'SAR',
            reference: 'TX123456',
            bank: BankType::FOODICS,
            meta: ['note' => 'Payment']
        );
    }

    public function test_it_creates_with_immutable_properties(): void
    {
        $transaction = $this->createTransaction();

        $this->assertEquals('2023-01-01', $transaction->getDate());
        $this->assertEquals('100.50', $transaction->getAmount());
        $this->assertEquals('SAR', $transaction->getCurrency());
        $this->assertEquals('TX123456', $transaction->getReference());
        $this->assertEquals(BankType::FOODICS, $transaction->getBank());
        $this->assertEquals(['note' => 'Payment'], $transaction->getMeta());
    }

    public function test_it_manages_client_id(): void
    {
        $transaction = $this->createTransaction();

        $this->expectException(\Error::class);
        $transaction->getClientId();

        $clientId = rand(1, 100);
        $transaction->setClientId($clientId);
        $this->assertEquals($clientId, $transaction->getClientId());
    }

    public function test_it_generates_uuid_on_creation()
    {
        $transaction = $this->createTransaction();

        $this->assertInstanceOf(\Ramsey\Uuid\UuidInterface::class, $transaction->getId());
        $this->assertNotEmpty($transaction->getId()->toString());
    }

    public function test_it_handles_different_bank_types()
    {
        $transaction = BankTransaction::create(
            date: '2023-01-01',
            amount: '200.00',
            currency: 'SAR',
            reference: 'TX654321',
            bank: BankType::ACME,
            meta: []
        );

        $this->assertEquals(BankType::ACME, $transaction->getBank());
    }

    public function test_it_initialized_correctly(): void
    {
        $transaction = BankTransaction::create(
            date: '2023-01-01',
            amount: '200.00',
            currency: 'SAR',
            reference: 'TX654321',
            bank: BankType::ACME,
            meta: [
                'note' => 'debt payment march',
                'internal_reference' => 'A462JE81'
            ]
        );

        $this->assertEquals('2023-01-01', $transaction->getDate());
        $this->assertEquals('200.00', $transaction->getAmount());
        $this->assertEquals('SAR', $transaction->getCurrency());
        $this->assertEquals('TX654321', $transaction->getReference());
        $this->assertEquals(BankType::ACME, $transaction->getBank());
        $this->assertEquals(['note' => 'debt payment march', 'internal_reference' => 'A462JE81'], $transaction->getMeta());
    }
}
