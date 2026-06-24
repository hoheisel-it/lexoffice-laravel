<?php

use HoheiselIT\Lexoffice\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post(
    config('lexoffice.webhook.path', 'lexoffice/webhook'),
    WebhookController::class
)->name('lexoffice.webhook');
