<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['client_id', 'wallet_id', 'source_transaction_id', 'amount', 'currency', 'type', 'status', 'meta'];

}
