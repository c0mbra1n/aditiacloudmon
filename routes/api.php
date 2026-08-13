<?php

use App\Http\Controllers\Api\v1\AgentApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/agent')->group(function () {
    Route::post('/heartbeat', [AgentApiController::class, 'heartbeat']);
    Route::post('/metrics', [AgentApiController::class, 'metrics']);
    Route::post('/services', [AgentApiController::class, 'services']);
    Route::post('/processes', [AgentApiController::class, 'processes']);
});
