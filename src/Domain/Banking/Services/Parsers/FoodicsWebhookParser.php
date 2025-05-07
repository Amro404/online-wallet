<?php

namespace Src\Domain\Banking\Services\Parsers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\Contracts\BankWebhookParserInterface;
use Src\Domain\Banking\DataTransferObjects\BankTransaction;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Src\Domain\Banking\ValueObjects\Money;

class FoodicsWebhookParser implements BankWebhookParserInterface
{
    const TRX_LINE_PATTERN = '/^\d{11},\d+(\.\d+)?#\d+#note\/.*\/internal_reference\/[A-Z0-9]+$/';

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

            $parts = explode('#', $line);

            $reference = $parts[1];
            $amount = Money::create($this->parseAmount($parts[0]), 'SAR');

            // Use transaction reference as array key to ensure uniqueness
            $transactions[$reference] = BankTransaction::create(
                date: $this->parseDate($parts[0]),
                amount: $amount->getAmount(),
                currency: $amount->getCurrency(),
                reference: $reference,
                bank: $payload->getBankIdentifier(),
                meta: $this->parseKeyValue($parts[2]),
            );
        }

        return array_values($transactions);
    }

    protected function parseDate(string $part): string
    {
        $date = substr($part, 0, 8);
        return Carbon::createFromFormat('Ymd', $date)->toDateString();
    }

    protected function parseAmount(string $part): string
    {
        return str_replace(',', '.', substr($part, 8));
    }

    protected function parseKeyValue(string $part): array
    {
        $keyValues = explode('/', $part);
        $keyValuesData = [];

        for ($i = 0; $i < count($keyValues) - 1; $i += 2) {
            $key = $keyValues[$i];
            $value = $keyValues[$i + 1] ?? null;
            $keyValuesData[$key] = $value;
        }

        return $keyValuesData;
    }
}
