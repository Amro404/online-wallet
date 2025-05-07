<?php

namespace Src\Domain\Wallet\Exceptions;

class WalletNotFoundException extends WalletBaseException
{
    public function __construct(int $clientId)
    {
        parent::__construct("Wallet not found for client ID: {$clientId}");
    }
}
