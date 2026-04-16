<?php

declare(strict_types=1);

use Webfloo\Http\Controllers\Api\LeadWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('leads')->middleware('throttle:30,1')->group(function () {
    Route::post('/webhook', [LeadWebhookController::class, 'store'])
        ->name('leads.webhook.store');

    Route::patch('/webhook/{externalId}', [LeadWebhookController::class, 'update'])
        ->name('leads.webhook.update');
});
