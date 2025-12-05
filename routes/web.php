<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para administradores
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::resource('/admin/usuarios', UsuarioController::class);
    Route::get('/admin/reportes', [ReporteController::class, 'index']);
});

// Rutas para técnicos
Route::middleware(['auth', 'role:tecnico,supervisor'])->group(function () {
    Route::get('/equipos', [EquipoController::class, 'index']);
    Route::get('/equipos/{id}', [EquipoController::class, 'show'])
        ->middleware('owner:equipo'); // Solo sus equipos
});

// Rutas para usuarios normales
Route::middleware(['auth', 'role:usuario'])->group(function () {
    Route::get('/mi-dashboard', [DashboardController::class, 'usuario']);
    Route::get('/mis-equipos', [EquipoController::class, 'misEquipos']);
});
