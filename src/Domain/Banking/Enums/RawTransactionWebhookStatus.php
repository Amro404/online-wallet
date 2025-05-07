<?php

namespace Src\Domain\Banking\Enums;

enum RawTransactionWebhookStatus: string
{
    case PENDING = 'PENDING';
    case PROCESSED = 'PROCESSED';
    case FAILED = 'FAILED';

}
