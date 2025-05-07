<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $primaryKey = "id";
    protected $keyType = 'string';

    protected $fillable = ['client_id', 'reference', 'amount', 'currency', 'bank_name', 'meta', 'date'];
}
