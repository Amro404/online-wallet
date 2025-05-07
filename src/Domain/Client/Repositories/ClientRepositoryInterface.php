<?php

namespace Src\Domain\Client\Repositories;

use App\Models\Client;

interface ClientRepositoryInterface
{
    public function getByMerchantId(string $merchantId): ?Client;
    public function getById(int $id): ?Client;
    public function getByReferenceId(string $reference): ?Client;


}
