<?php

namespace Src\Domain\Banking\Contracts;

use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;

interface BankWebhookParserInterface
{
    public function parse(BankWebhookPayload $payload): array;
}
