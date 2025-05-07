<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Src\Domain\Wallet\Contracts\WalletHolderInterface;
use Src\Domain\Wallet\Services\WalletDomainService;

class Client extends Model implements WalletHolderInterface
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'provider_merchant_id'];

    protected static function booted()
    {
        static::created(function ($client) {
            $client->initializeWallet();
        });
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function initializeWallet(): void
    {
        app(WalletDomainService::class)->createWallet($this);
    }
}
