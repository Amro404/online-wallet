<?php

namespace Src\Domain\Wallet\Enums;

enum WalletTransactionType: string
{
    case DEPOSIT = 'DEPOSIT';
    case WITHDRAW = 'WITHDRAW';
}
