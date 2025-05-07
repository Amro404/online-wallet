<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentTransferRequest;
use Illuminate\Http\JsonResponse;
use Src\Domain\Payment\Enums\PaymentStatus;
use Src\Domain\Payment\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(public PaymentService $paymentService) {}

    public function transfer(PaymentTransferRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $response = $this->paymentService->sendPaymentRequest($validated);

            return response()->json([
                'status' => $response->getStatus() == PaymentStatus::COMPLETED ? 'success' : 'failed',
                'reference' => $response->getReference(),
                'bank_reference' => $response->getBankReference(),
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment processing failed',
            ], 500);
        }

    }


}
