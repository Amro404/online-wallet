<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawTransactionWebhook extends Model
{
    protected $fillable = ['client_id', 'payload', 'headers', 'bank_name', 'status', 'received_at'];
}
