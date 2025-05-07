<?php

namespace Src\Domain\Banking\Services\Parsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\Contracts\BankWebhookParserInterface;
use Src\Domain\Banking\DataTransferObjects\BankTransaction;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\ValueObjects\Money;

class AcmeWebhookParser implements BankWebhookParserInterface
{
    const TRX_LINE_PATTERN = '/^\d+,\d{2}\/\/\d+\/\/\d{8}$/';

    public function parse(BankWebhookPayload $payload): array
    {
        $content = trim($payload->getContent());
        if (empty($content)) return [];

        $lines = preg_split('/\r\n|\r|\n/', $content);
        $transactions = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) continue;

            if (!preg_match(self::TRX_LINE_PATTERN, $line)) {
                Log::warning("Skipping invalid line in webhook payload", ['line' => $line]);
                continue;
            }

            $parts = explode('//', $line);

            $amount = Money::create($parts[0], 'SAR');
            $reference = $parts[1];
            $date = $this->parseDate($parts[2]);

            $transactions[$reference] = BankTransaction::create(
                date: $date,
                amount: $amount->getAmount(),
                currency: $amount->getCurrency(),
                reference: $reference,
                bank: $payload->getBankIdentifier(),
                meta: []
            );
        }

        return array_values($transactions);
    }

    protected function parseDate(string $date): string
    {
        return Carbon::createFromFormat('Ymd', $date)->toDateString();
    }
}
