<?php

namespace Tests\Unit\Domain\Wallet\Services;

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Models\Wallet;
use Src\Domain\Wallet\Services\PrepareService;
use Src\Domain\Wallet\Services\ConsistencyService;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;
use Src\Domain\Wallet\Exceptions\AmountInvalidBaseException;

class PrepareServiceTest extends TestCase
{
    private ConsistencyService $consistencyService;
    private PrepareService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consistencyService = Mockery::mock(ConsistencyService::class);
        $this->service = new PrepareService($this->consistencyService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createWallet(int $clientId = 1, int $walletId = 100): Wallet
    {
        $wallet = new Wallet();
        $wallet->id = $walletId;
        $wallet->client_id = $clientId;
        return $wallet;
    }

    public function test_deposit_creates_correct_transaction(): void
    {
        $wallet = $this->createWallet();
        $amount = 100.50;
        $meta = ['note' => 'test deposit'];

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->andReturn(true)
            ->once();

        $transaction = $this->service->deposit($wallet, $amount, $meta);

        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals($wallet->client_id, $transaction->getClientId());
        $this->assertEquals($wallet->id, $transaction->getWalletId());
        $this->assertEquals(WalletTransactionType::DEPOSIT->value, $transaction->getType());
        $this->assertEquals($amount, $transaction->getAmount());
        $this->assertEquals(WalletTransactionStatus::SUCCESSFUL->value, $transaction->getStatus());
        $this->assertEquals($meta, $transaction->getMeta());
    }

    public function test_deposit_throws_for_invalid_amount(): void
    {
        $wallet = $this->createWallet();
        $invalidAmount = -10.00;

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($invalidAmount)
            ->once()
            ->andThrow(new AmountInvalidBaseException());

        $this->expectException(AmountInvalidBaseException::class);
        $this->service->deposit($wallet, $invalidAmount, null);
    }

    public function test_deposit_with_custom_status(): void
    {
        $wallet = $this->createWallet();
        $amount = 50.00;
        $status = WalletTransactionStatus::PENDING;

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->andReturn(true)
            ->once();

        $transaction = $this->service->deposit($wallet, $amount, null, $status);

        $this->assertEquals($status->value, $transaction->getStatus());
    }

    public function test_withdraw_creates_correct_transaction(): void
    {
        $wallet = $this->createWallet();
        $amount = 50.25;
        $meta = ['note' => 'test withdrawal'];

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->andReturn(true)
            ->once();

        $transaction = $this->service->withdraw($wallet, $amount, $meta);

        $this->assertInstanceOf(WalletTransaction::class, $transaction);
        $this->assertEquals($wallet->client_id, $transaction->getClientId());
        $this->assertEquals($wallet->id, $transaction->getWalletId());
        $this->assertEquals(WalletTransactionType::WITHDRAW->value, $transaction->getType());
        $this->assertEquals($amount * -1, $transaction->getAmount());
        $this->assertEquals(WalletTransactionStatus::SUCCESSFUL->value, $transaction->getStatus());
        $this->assertEquals($meta, $transaction->getMeta());
    }

    public function test_withdraw_throws_for_invalid_amount(): void
    {
        $wallet = $this->createWallet();
        $invalidAmount = 0;

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($invalidAmount)
            ->once()
            ->andThrow(new AmountInvalidBaseException());

        $this->expectException(AmountInvalidBaseException::class);
        $this->service->withdraw($wallet, $invalidAmount, null);
    }

    public function test_withdraw_with_custom_status(): void
    {
        $wallet = $this->createWallet();
        $amount = 30.00;
        $status = WalletTransactionStatus::FAILED;

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->andReturn(true)
            ->once();

        $transaction = $this->service->withdraw($wallet, $amount, null, $status);

        $this->assertEquals($status->value, $transaction->getStatus());
    }

    public function test_deposit_with_null_meta(): void
    {
        $wallet = $this->createWallet();
        $amount = 75.00;

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->andReturn(true)
            ->once();

        $transaction = $this->service->deposit($wallet, $amount, null);

        $this->assertNull($transaction->getMeta());
    }

    public function test_withdraw_with_empty_meta(): void
    {
        $wallet = $this->createWallet();
        $amount = 25.00;
        $meta = [];

        $this->consistencyService->shouldReceive('checkPositive')
            ->with($amount)
            ->once();

        $transaction = $this->service->withdraw($wallet, $amount, $meta);

        $this->assertEquals([], $transaction->getMeta());
    }
}
