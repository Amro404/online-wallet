<?php

namespace Tests\Unit\Domain\Wallet\Services;

use App\Models\Wallet;
use Src\Domain\Wallet\Services\WalletDomainService;
use Tests\TestCase;
use Src\Domain\Wallet\Services\ConsistencyService;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Exceptions\AmountInvalidException;
use Src\Domain\Wallet\Exceptions\BalanceIsEmptyException;
use Src\Domain\Wallet\Exceptions\InsufficientFundsException;
use Mockery;

class ConsistencyServiceTest extends TestCase
{
    private WalletHolderInterface $walletHolder;
    private ConsistencyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletDomainService = Mockery::mock(WalletDomainService::class);
        $this->service = new ConsistencyService($this->walletDomainService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_check_positive_with_valid_amount(): void
    {
        $this->service->checkPositive(0.01);
        $this->service->checkPositive(100.00);
        $this->service->checkPositive(1);

        $this->assertTrue(true);
    }

    public function test_check_positive_throws_for_zero_amount(): void
    {
        $this->expectException(AmountInvalidException::class);
        $this->service->checkPositive(0);
    }

    public function test_check_positive_throws_for_negative_amount(): void
    {
        $this->expectException(AmountInvalidException::class);
        $this->service->checkPositive(-0.01);
    }

    public function test_check_potential_with_sufficient_funds(): void
    {
        $walletHolder = Mockery::mock(WalletHolderInterface::class);
        $wallet = new Wallet([
            'balance' => 100.00,
        ]);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($walletHolder)
            ->andReturn($wallet);

        $this->service->checkPotential($walletHolder, 50.00);

        $this->assertTrue(true);
    }

    public function test_check_potential_throws_for_empty_balance(): void
    {
        $walletHolder = Mockery::mock(WalletHolderInterface::class);
        $wallet = new Wallet([
            'balance' => 0,
        ]);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($walletHolder)
            ->andReturn($wallet);

        $this->expectException(BalanceIsEmptyException::class);
        $this->service->checkPotential($walletHolder, 50.00);
    }

    public function test_check_potential_throws_for_insufficient_funds(): void
    {
        $walletHolder = Mockery::mock(WalletHolderInterface::class);
        $wallet = new Wallet([
            'balance' => 30.00,
        ]);


        $this->walletDomainService->shouldReceive('getWallet')
            ->with($walletHolder)
            ->andReturn($wallet);

        $this->expectException(InsufficientFundsException::class);
        $this->service->checkPotential($walletHolder, 50.00);
    }

    public function test_check_potential_allows_zero_amount(): void
    {
        $walletHolder = Mockery::mock(WalletHolderInterface::class);
        $wallet = new Wallet([
            'balance' => 0
        ]);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($walletHolder)
            ->andReturn($wallet);

        $this->expectException(InsufficientFundsException::class);

        $this->service->checkPotential($walletHolder, 0);

        $this->assertTrue(false);
    }

    public function test_can_withdraw_with_sufficient_funds(): void
    {
        $this->assertTrue($this->service->canWithdraw(100.00, 50.00));
        $this->assertTrue($this->service->canWithdraw(50.00, 50.00));
    }

    public function test_can_withdraw_with_insufficient_funds(): void
    {
        $this->assertFalse($this->service->canWithdraw(30.00, 50.00));
        $this->assertFalse($this->service->canWithdraw(0, 50.00));
    }

    public function test_can_withdraw_with_invalid_amount(): void
    {
        $this->assertFalse($this->service->canWithdraw(100.00, 0));
        $this->assertFalse($this->service->canWithdraw(100.00, -10.00));
    }

    public function test_check_potential_with_null_balance(): void
    {
        $walletHolder = Mockery::mock(WalletHolderInterface::class);
        $wallet = new Wallet([
            'balance' => null,
        ]);


        $this->walletDomainService->shouldReceive('getWallet')
            ->with($walletHolder)
            ->andReturn($wallet);

        $this->expectException(BalanceIsEmptyException::class);
        $this->service->checkPotential($walletHolder, 50.00);
    }

}
