<?php

use App\Http\Controllers\Future\FutureIntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'service' => 'LedgerScope API Backend',
        'version' => '1.0.0',
        'environment' => app()->environment(),
    ]);
});

Route::middleware('auth')->get('/future/integrations', FutureIntegrationController::class)
    ->name('future.integrations');
