<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateBankWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        // in a real-world scenario, we must verify the signature of the incoming webhook to ensure it is not a forgery.
        $bank = $request->route('bank');
        $signature = $request->header('X-Signature') ?? null;
        $secret = config("bank_secrets.{$bank}.webhook_secret");
        $merchantId = $request->header('X-Merchant-Id');

        if ( ! $merchantId || ! $signature) {
            return response()->json(
                data: ['message' => 'Missing headers.'],
                status: 400,
            );
        }

        $isValid = match ($bank) {
            'foodics', 'acme' => $this->isValidSignature($merchantId, $signature, $secret),
            default => false,
        };


        if ( ! $isValid) {
            return response()->json(
                data: ['message' => 'Invalid signature.'],
                status: 403,
            );
        }


        return $next($request);
    }


    private function isValidSignature(
        string $merchantId,
        string $signature,
        string $secret,
    ): bool {

        return hash_hmac('sha256', $merchantId, $secret)
            == hash_hmac('sha256', $merchantId, $signature);
    }
}
