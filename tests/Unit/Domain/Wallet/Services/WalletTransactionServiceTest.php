<?php

namespace Tests\Unit\Domain\Wallet\Services;

use App\Models\Wallet;
use Tests\TestCase;
use Mockery;
use App\Models\WalletTransaction as WalletTransactionModel;
use Src\Domain\Wallet\Services\WalletTransactionService;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\DataTransferObjects\WalletTransaction;
use Src\Domain\Wallet\Enums\WalletTransactionStatus;
use Src\Domain\Wallet\Enums\WalletTransactionType;
use Src\Domain\Wallet\Events\WalletTransactionCreated;
use Src\Domain\Wallet\Repositories\WalletTransactionRepositoryInterface;
use Src\Domain\Wallet\Services\WalletDomainService;
use Src\Domain\Wallet\Services\PrepareService;
use Src\Domain\Wallet\Services\ConsistencyService;
use Illuminate\Support\Facades\Event;

class WalletTransactionServiceTest extends TestCase
{

    private WalletTransactionRepositoryInterface $walletTransactionRepo;
    private WalletDomainService $walletDomainService;
    private PrepareService $prepareService;
    private ConsistencyService $consistencyService;
    private WalletTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletTransactionRepo = Mockery::mock(WalletTransactionRepositoryInterface::class);
        $this->walletDomainService = Mockery::mock(WalletDomainService::class);
        $this->prepareService = Mockery::mock(PrepareService::class);
        $this->consistencyService = Mockery::mock(ConsistencyService::class);

        $this->service = new WalletTransactionService(
            $this->walletTransactionRepo,
            $this->walletDomainService,
            $this->prepareService,
            $this->consistencyService
        );

        Event::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createWalletHolder(int $clientId = 1): WalletHolderInterface
    {
        $holder = Mockery::mock(WalletHolderInterface::class);
        $holder->shouldReceive('getClientId')->andReturn($clientId);
        return $holder;
    }

    private function createTransactionDto(
        int $clientId = 1,
        int $walletId = 1,
        float $amount = 100.00,
        WalletTransactionType $type = WalletTransactionType::DEPOSIT,
        WalletTransactionStatus $status = WalletTransactionStatus::SUCCESSFUL,
        ?array $meta = null
    ): WalletTransaction {
        return WalletTransaction::create(
            clientId: $clientId,
            walletId: $walletId,
            type: $type,
            amount: $amount,
            status: $status,
            meta: $meta
        );
    }

    public function test_initiate_deposit_creates_transaction(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 100.00;
        $meta = [
            'source_id' => '1234',
            'source_type' => 'bank_webhook',
        ];

        $wallet = new Wallet([
            'id' => 1,
            'client_id' => 1,
        ]);

        $dto = $this->createTransactionDto($amount);
        $transaction = new WalletTransactionModel();

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        $this->prepareService->shouldReceive('deposit')
            ->with($wallet, $amount, $meta, WalletTransactionStatus::SUCCESSFUL)
            ->andReturn($dto);

        $this->walletTransactionRepo->shouldReceive('create')
            ->with($dto)
            ->andReturn($transaction);

        $this->walletDomainService->shouldReceive('updateBalance')
            ->with($holder, $amount);

        $result = $this->service->initiateTransaction(
            $holder,
            WalletTransactionType::DEPOSIT,
            $amount,
            $meta
        );

        $this->assertSame($transaction, $result);
        Event::assertDispatched(WalletTransactionCreated::class);
    }

    public function test_initiate_withdraw_creates_transaction(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 50.00;
        $meta = [
            'source_id' => '1234',
            'source_type' => 'payment_request',
        ];

       $wallet = new Wallet([
            'id' => 1,
            'client_id' => 1,
        ]);

        $dto = $this->createTransactionDto(
            amount: -1 * $amount,
            type: WalletTransactionType::WITHDRAW
        );

        $transaction = new WalletTransactionModel();

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        $this->prepareService->shouldReceive('withdraw')
            ->with($wallet, $amount, $meta, WalletTransactionStatus::SUCCESSFUL)
            ->andReturn($dto);

        $this->walletTransactionRepo->shouldReceive('create')
            ->with($dto)
            ->andReturn($transaction);

        $this->walletDomainService->shouldReceive('updateBalance')
            ->with($holder, -$amount);

        $result = $this->service->initiateTransaction(
            $holder,
            WalletTransactionType::WITHDRAW,
            $amount,
            $meta
        );

        $this->assertSame($transaction, $result);
        Event::assertDispatched(WalletTransactionCreated::class);
    }


    public function test_confirm_transaction_creates_and_updates(): void
    {
        $holder = $this->createWalletHolder();
        $dto = $this->createTransactionDto();
        $transaction = new WalletTransactionModel();

        $this->walletTransactionRepo->shouldReceive('create')
            ->with($dto)
            ->andReturn($transaction);

        $this->walletDomainService->shouldReceive('updateBalance')
            ->with($holder, $dto->getAmount());

        $result = $this->service->confirmTransaction($holder, $dto);

        $this->assertSame($transaction, $result);
        Event::assertDispatched(WalletTransactionCreated::class);
    }

    public function test_initiate_withdraw_with_custom_status(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 100.00;
        $status = WalletTransactionStatus::PENDING;
        $wallet = new Wallet([
            'id' => 1,
            'client_id' => 1,
        ]);

        $dto = $this->createTransactionDto(1, 1, $amount, WalletTransactionType::WITHDRAW, $status);
        $transaction = new WalletTransactionModel();

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        $this->prepareService->shouldReceive('withdraw')
            ->with($wallet, $amount, null, $status)
            ->andReturn($dto);

        $this->walletTransactionRepo->shouldReceive('create')
            ->with($dto)
            ->andReturn($transaction);

        $this->walletDomainService->shouldReceive('updateBalance')
            ->with($holder, $amount);

        $result = $this->service->initiateTransaction(
            $holder,
            WalletTransactionType::WITHDRAW,
            $amount,
            null,
            $status
        );

        $this->assertSame($transaction, $result);
    }

    public function test_initiate_withdraw_with_empty_meta(): void
    {
        $holder = $this->createWalletHolder();
        $amount = 50.00;
        $wallet = new Wallet([
            'id' => 1,
            'client_id' => 1,
        ]);

        $dto = $this->createTransactionDto(
            amount: -$amount,
            type: WalletTransactionType::WITHDRAW,
            meta: []
        );
        $transaction = new WalletTransactionModel();

        $this->walletDomainService->shouldReceive('getWallet')
            ->with($holder)
            ->andReturn($wallet);

        $this->prepareService->shouldReceive('withdraw')
            ->with($wallet, $amount, [], WalletTransactionStatus::SUCCESSFUL)
            ->andReturn($dto);

        $this->walletTransactionRepo->shouldReceive('create')
            ->with($dto)
            ->andReturn($transaction);

        $this->walletDomainService->shouldReceive('updateBalance')
            ->with($holder, -$amount);

        $result = $this->service->initiateTransaction(
            $holder,
            WalletTransactionType::WITHDRAW,
            $amount,
            []
        );

        $this->assertSame($transaction, $result);
    }
}
