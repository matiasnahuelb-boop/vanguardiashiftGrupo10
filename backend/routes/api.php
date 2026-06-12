<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProfesionalController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/profesionales', [ProfesionalController::class, 'index']);
    Route::get('/profesionales/{id}/disponibilidad', [ProfesionalController::class, 'disponibilidad']);

    Route::middleware('role:cliente')->group(function () {
        Route::post('/turnos', [TurnoController::class, 'store']);
    });

    Route::middleware('role:profesional')->group(function () {
        Route::get('/turnos/profesional', [TurnoController::class, 'agenda']);
        Route::put('/turnos/{id}/estado', [TurnoController::class, 'updateEstado']);
    });
});
