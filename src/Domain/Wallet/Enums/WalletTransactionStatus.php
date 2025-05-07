<?php

namespace Src\Domain\Wallet\Enums;

enum WalletTransactionStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESSFUL = 'SUCCESSFUL';
    case FAILED = 'FAILED';
}
