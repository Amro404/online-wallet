<?php

namespace Tests\Unit\Domain\Banking\Actions;

use Tests\TestCase;
use Src\Domain\Banking\Actions\ProcessBankTransactionsAction;
use Mockery;
use App\Models\Client;
use Src\Domain\Banking\DataTransferObjects\BankTransaction;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Repositories\BankTransactionRepositoryInterface;
use Src\Domain\Client\Services\ClientService;
use Src\Domain\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessBankTransactionsActionTest extends TestCase
{
    use RefreshDatabase;

    private BankTransactionRepositoryInterface $bankTransactionRepo;
    private ClientService $clientService;
    private WalletService $walletService;
    private ProcessBankTransactionsAction $action;

    protected function setUp(): void
    {

        parent::setUp();

        $this->bankTransactionRepo = Mockery::mock(BankTransactionRepositoryInterface::class);
        $this->clientService = Mockery::mock(ClientService::class);
        $this->walletService = Mockery::mock(WalletService::class);

        $this->action = new ProcessBankTransactionsAction(
            $this->bankTransactionRepo,
            $this->clientService,
            $this->walletService
        );

        $this->seed();
    }

    private function createTransaction(string $reference, float $amount, BankType $bankType): BankTransaction
    {
        $bankTransaction = BankTransaction::create(
            date: '2023-01-01',
            amount: $amount,
            currency: 'SAR',
            reference: $reference,
            bank: $bankType,
            meta: ['note' => 'Payment']
        );

        $bankTransaction->setClientId(1);

        return $bankTransaction;
    }

    public function test_execute_with_empty_transactions_does_nothing(): void
    {
        $this->bankTransactionRepo->shouldNotReceive('bulkInsert');
        $this->clientService->shouldNotReceive('getById');
        $this->walletService->shouldNotReceive('deposit');

        $this->action->execute([]);
        $this->assertTrue(true, 'No methods were called on dependencies');
    }

    public function test_execute_filters_out_duplicate_transactions(): void
    {
        $transaction1 = $this->createTransaction('REF123', 200, BankType::FOODICS);
        $transaction2 = $this->createTransaction('REF123', 200, BankType::FOODICS);
        $transaction3 = $this->createTransaction('REF124', 300, BankType::FOODICS);

        $this->bankTransactionRepo->shouldReceive('findExistingReferences')
            ->andReturn(['REF123']);

        $this->bankTransactionRepo->shouldReceive('bulkInsert')
            ->with([$transaction3])
            ->once();

        $client = Client::find(1);

        $this->clientService->shouldReceive('getById')->with(1)->andReturn($client);

        $this->walletService->shouldReceive('deposit')->with(
            $client,
            300,
            ['source_id' => $transaction3->getId(), 'source_type' => 'bank_transaction']
        )->once();

        $this->action->execute([$transaction1, $transaction2, $transaction3]);
    }

    public function test_processes_all_transactions_when_no_duplicates_exist(): void
    {
        $transaction1 = $this->createTransaction('REF123', 200, BankType::FOODICS);
        $transaction2 = $this->createTransaction('REF124', 300, BankType::FOODICS);

        $this->bankTransactionRepo->shouldReceive('findExistingReferences')
            ->andReturn([]);

        $this->bankTransactionRepo->shouldReceive('bulkInsert')
            ->with([$transaction1, $transaction2])
            ->once();

        $client = Client::find(1);

        $this->clientService->shouldReceive('getById')->with(1)->andReturn($client);

        $this->walletService->shouldReceive('deposit')->times(2);

        $this->action->execute([$transaction1, $transaction2]);
    }

    public function test_applies_transactions_to_wallet_correctly(): void
    {
        $transaction1 = $this->createTransaction('REF123', 200, BankType::FOODICS);
        $transaction2 = $this->createTransaction('REF124', 300, BankType::FOODICS);

        $this->bankTransactionRepo->shouldReceive('findExistingReferences')
            ->andReturn([]);

        $this->bankTransactionRepo->shouldReceive('bulkInsert')
            ->with([$transaction1, $transaction2])
            ->once();

        $client = Client::find(1);

        $this->clientService->shouldReceive('getById')->with(1)->andReturn($client);

        $this->walletService->shouldReceive('deposit')->with(
            $client,
            200,
            ['source_id' => $transaction1->getId(), 'source_type' => 'bank_transaction']
        )->once();

        $this->walletService->shouldReceive('deposit')->with(
            $client,
            300,
            ['source_id' => $transaction2->getId(), 'source_type' => 'bank_transaction']
        )->once();

        $this->action->execute([$transaction1, $transaction2]);
    }
}
