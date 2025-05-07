<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'sender_account_number' => 'required|string',
            'receiver_account_number' => 'required|string|different:sender_account_number',
            'receiver_bank_code' => 'required|string',
            'receiver_beneficiary_name' => 'required|string',
            'payment_type' => 'string',
            'charge_details' => 'string',
            'notes' => 'sometimes|array',
            'notes.*' => 'string',
        ];
    }
}
