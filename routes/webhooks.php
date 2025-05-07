<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankWebhookController;

Route::post('banks/{bank}/transactions', BankWebhookController::class)
    ->middleware('validate.bank.webhook');
