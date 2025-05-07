<?php

namespace Src\Domain\Banking\Factories;

use Src\Domain\Banking\Contracts\BankWebhookParserInterface;
use Src\Domain\Banking\Enums\BankType;
use Src\Domain\Banking\Services\Parsers\AcmeWebhookParser;
use Src\Domain\Banking\Services\Parsers\FoodicsWebhookParser;

class BankWebhookParserFactory
{
    public function fromBank(BankType $bankType): BankWebhookParserInterface
    {
        return match ($bankType) {
            BankType::FOODICS => app(FoodicsWebhookParser::class),
            BankType::ACME => app(AcmeWebhookParser::class),
            default => throw new \InvalidArgumentException(
                "No parser available for bank type: {$bankType->value}"
            ),
        };
    }

}
