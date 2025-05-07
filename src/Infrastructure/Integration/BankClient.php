<?php

namespace Src\Infrastructure\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankClient
{
    public function post(string $url, string $xml, array $headers = []): string
    {
        Log::info('Sending request to bank', ['url' => $url]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->retry(3, 100)
                ->withBody($xml, 'application/xml')
                ->post($url);

            if ($response->failed()) {
                throw new \RuntimeException("Bank API request failed: {$response->status()}");
            }

            return $response->body();

        } catch (\Exception $e) {
            Log::error('Bank API request failed', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            throw $e;
        }
    }

    private function parseXmlResponse(string $xml): array
    {
        return [];
    }
}
