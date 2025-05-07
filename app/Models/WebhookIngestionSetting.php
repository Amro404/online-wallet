<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookIngestionSetting extends Model
{
    protected $fillable = ['client_id', 'bank_name', 'paused', 'reason', 'paused_at', 'resumed_at'];
}
