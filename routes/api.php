<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EquipoController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para equipos
Route::apiResource('equipos', EquipoController::class);
Route::apiResource('sensores', SensorController::class);

// Ruta específica para almacenar lecturas de sensores
Route::post('sensores/{sensorId}/lecturas', [App\Http\Controllers\SensorController::class, 'almacenarLectura']);

Route::get('dashboard', [DashboardController::class, 'index']);