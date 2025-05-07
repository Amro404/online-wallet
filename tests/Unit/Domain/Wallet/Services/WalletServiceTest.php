<?php

namespace Tests\Unit\Domain\Wallet\Services;

use Tests\TestCase;
use Mockery;
use App\Models\WalletTransaction;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Services\WalletService;
use Src\Domain\Wallet\Repositories\WalletRepositoryInterface;
use Src\Domain\Wallet\Services\WalletTransactionService;
use Src\Domain\Wallet\Services\ConsistencyService;
use Src\Domain\Wallet\Services\LockService;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;

class WalletServiceTest extends TestCase
{
    private WalletRepositoryInterface $walletRepository;
    private WalletTransactionService $walletTransactionService;
    private ConsistencyService $consistencyService;
    private LockService $lockService;
    private WalletService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletRepository = Mockery::mock(WalletRepositoryInterface::class);
        $this->walletTransactionService = Mockery::mock(WalletTransactionService::class);
        $this->consistencyService = Mockery::mock(ConsistencyService::class);
        $this->lockService = Mockery::mock(LockService::class);

        $this->service = new WalletService(
            $this->walletRepository,
            $this->walletTransactionService,
            $this->consistencyService,
            $this->lockService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createWalletHolder(int $walletId = 1): WalletHolderInterface
    {
        $holder = Mockery::mock(WalletHolderInterface::class);
        $holder->shouldReceive('getWalletId')->andReturn($walletId);
        return $holder;
    }

    public function test_deposit_successfully(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 100.00;
        $meta = [
            'source_id' => '1234',
            'source_type' => 'bank_webhook',
        ];

        $transaction = new WalletTransaction();

        $this->lockService->shouldReceive('block')
            ->with($holder, Mockery::type('callable'))
            ->andReturnUsing(function ($holder, $callback) {
                return $callback();
            });

        $this->walletTransactionService->shouldReceive('initiateTransaction')
            ->with($holder, WalletTransactionType::DEPOSIT, $amount, $meta, WalletTransactionStatus::SUCCESSFUL)
            ->andReturn($transaction);

        $result = $this->service->deposit($holder, $amount, $meta);

        $this->assertSame($transaction, $result);
    }

    public function test_withdraw_successfully(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 50.00;
        $meta = [
            'source_id' => '1234',
            'source_type' => 'payment_request',
        ];
        $transaction = new WalletTransaction();

        $this->lockService->shouldReceive('block')
            ->with($holder, Mockery::type('callable'))
            ->andReturnUsing(function ($holder, $callback) {
                return $callback();
            });

        $this->consistencyService->shouldReceive('checkPotential')
            ->with($holder, $amount);

        $this->walletTransactionService->shouldReceive('initiateTransaction')
            ->with($holder, WalletTransactionType::WITHDRAW, $amount, $meta, WalletTransactionStatus::SUCCESSFUL)
            ->andReturn($transaction);

        $result = $this->service->withdraw($holder, $amount, $meta);

        $this->assertSame($transaction, $result);
    }

    public function test_balance_returns_correct_value(): void
    {
        $holder = $this->createWalletHolder();
        $expectedBalance = 500.00;

        $this->walletRepository->shouldReceive('balance')
            ->with($holder)
            ->andReturn($expectedBalance);

        $result = $this->service->balance($holder);

        $this->assertEquals($expectedBalance, $result);
    }

    public function test_balance_returns_zero_when_null(): void
    {
        $holder = $this->createWalletHolder();

        $this->walletRepository->shouldReceive('balance')
            ->with($holder)
            ->andReturn(null);

        $result = $this->service->balance($holder);

        $this->assertEquals(0, $result);
    }

}
