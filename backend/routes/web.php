<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'service' => 'LedgerScope API Backend',
        'version' => '1.0.0',
        'environment' => app()->environment(),
    ]);
});
