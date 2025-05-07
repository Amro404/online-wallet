<?php

namespace Tests\Unit\Domain\Wallet\Services;

use App\Models\Wallet;
use Tests\TestCase;
use Mockery;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Services\LockService;
use Src\Domain\Wallet\Services\WalletDomainService;
use Exception;

class LockServiceTest extends TestCase
{
    private WalletDomainService $walletDomainService;
    private LockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletDomainService = Mockery::mock(WalletDomainService::class);
        $this->service = new LockService($this->walletDomainService);
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

    private function mockWallet(int $walletId = 1): object
    {
        return new Wallet(['id' => $walletId]);
    }

    public function test_block_successfully_acquires_lock_and_executes_callback(): void
    {
        $holder = $this->createWalletHolder();
        $wallet = $this->mockWallet();
        $expectedResult = 'test_result';
        $callback = function() use ($expectedResult) { return $expectedResult; };

        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')->with(5)->andReturn(true);
        $lock->shouldReceive('release')->atLeast()->once();

        Cache::shouldReceive('lock')
            ->with('wallet_lock::' . $wallet->id, 10)
            ->andReturn($lock);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        DB::shouldReceive('transaction')
            ->with(Mockery::type('callable'))
            ->andReturn($expectedResult);

        $result = $this->service->block($holder, $callback);

        $this->assertEquals($expectedResult, $result);
    }

    public function test_block_releases_lock_on_callback_exception(): void
    {
        $holder = $this->createWalletHolder();
        $wallet = $this->mockWallet();
        $exception = new \Exception('Test exception');

        $callback = function() use ($exception) { throw $exception; };

        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')->with(5)->andReturn(true);
        $lock->shouldReceive('release')->atLeast()->once();

        Cache::shouldReceive('lock')
            ->with('wallet_lock::' . $wallet->id, 10)
            ->andReturn($lock);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        DB::shouldReceive('transaction')
            ->with(Mockery::type('callable'))
            ->andThrow($exception);

        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        $this->service->block($holder, $callback);
    }

    public function test_block_retries_on_lock_failure(): void
    {
        $holder = $this->createWalletHolder();
        $wallet = $this->mockWallet();
        $expectedResult = 'test_result';
        $callback = function() use ($expectedResult) { return $expectedResult; };

        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->with(5)
            ->andReturn(false, false, true); // Fail twice, then succeed
        $lock->shouldReceive('release')->atLeast()->once();

        Cache::shouldReceive('lock')
            ->with('wallet_lock::' . $wallet->id, 10)
            ->andReturn($lock);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        DB::shouldReceive('transaction')
            ->with(Mockery::type('callable'))
            ->andReturn($expectedResult);

        $result = $this->service->block($holder, $callback);

        $this->assertEquals($expectedResult, $result);
    }

    public function test_block_handles_lock_timeout_exception(): void
    {
        $holder = $this->createWalletHolder();
        $wallet = $this->mockWallet();
        $callback = function() { return 'test_result'; };

        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->with(5)
            ->andThrow(new LockTimeoutException());

        $lock->shouldReceive('release')->never();

        Cache::shouldReceive('lock')
            ->with('wallet_lock::' . $wallet->id, 10)
            ->andReturn($lock);

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        $this->expectException(LockTimeoutException::class);

        $this->service->block($holder, $callback);
    }

}
