<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'client_id',
        'reference',
        'amount',
        'currency',
        'sender_account_number',
        'receiver_bank_code',
        'receiver_account_number',
        'beneficiary_name',
        'payment_type',
        'charge_details',
        'status',
        'failure_reason',
        'notes',
        'transfer_date',
    ];

}
