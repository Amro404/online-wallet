<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBankWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Domain\Banking\DataTransferObjects\BankWebhookPayload;
use Symfony\Component\HttpFoundation\Response;

class BankWebhookController extends Controller
{
    public function __invoke(Request $request, string $bank): JsonResponse
    {
        try {
            $payload = BankWebhookPayload::create(
                bank: $bank,
                content: $request->getContent(),
                merchantId: $request->header('X-Merchant-Id'),
                headers: $request->headers->all()
            );

            ProcessBankWebhook::dispatchSync($payload);

            return response()->json(
                data: ['message' => 'Webhook received successfully'],
                status: Response::HTTP_ACCEPTED
            );

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            return response()->json(['error' =>  $exception->getMessage()]);
        }

    }

}
