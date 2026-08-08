<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/whatsapp', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp');

Route::get('/webhooks/facebook-leads', [\App\Http\Controllers\Api\FacebookLeadsWebhookController::class, 'verify'])->name('webhooks.facebook-leads.verify');
Route::post('/webhooks/facebook-leads', [\App\Http\Controllers\Api\FacebookLeadsWebhookController::class, 'handle'])->name('webhooks.facebook-leads');

Route::post('/webhooks/google-ads-leads', [\App\Http\Controllers\Api\GoogleAdsLeadsWebhookController::class, 'handle'])->name('webhooks.google-ads-leads');

Route::middleware(['auth:sanctum', 'role:Admin|Staff|Telecaller', 'client.active'])->prefix('v1')->group(function () {
    Route::apiResource('leads', LeadController::class);
});
