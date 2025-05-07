<?php

namespace Src\Infrastructure\Repositories;

use App\Models\Client;
use Src\Domain\Client\Repositories\ClientRepositoryInterface;

class EloquentClientRepository implements ClientRepositoryInterface
{
    public function getByMerchantId(string $merchantId): ?Client
    {
        return Client::query()->where('provider_merchant_id', $merchantId)->first();
    }

    public function getById(int $id): ?Client
    {
        return Client::query()->findOrFail($id);
    }

    public function getByReferenceId(string $reference): ?Client
    {
        return Client::query()->where('reference_id', $reference)->first();
    }
}
