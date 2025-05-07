<?php

namespace Src\Domain\Client\Services;

use App\Models\Client;
use Src\Domain\Client\Repositories\ClientRepositoryInterface;

class ClientService
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository,
    ) {}

    public function getByMerchantId(string $merchantId): ?Client
    {
        $client = $this->clientRepository->getByMerchantId($merchantId);

        if ($client == null) {
            throw new \Exception('Client not found');
        }

        return $client;
    }

    public function getByReferenceId(string $merchantId): ?Client
    {
        $client = $this->clientRepository->getByReferenceId($merchantId);

        if ($client == null) {
            throw new \Exception('Client not found');
        }

        return $client;
    }


    public function getById(int $clientId): Client
    {
        return $this->clientRepository->getById($clientId);
    }

}
