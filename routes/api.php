<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Api\ConversionController;
use App\Http\Controllers\Api\TerritorioController;
use App\Http\Controllers\BuzonController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\TitularController;
use Illuminate\Support\Facades\Route;

// API pública del sitio (sección 05), prefijo /api/v1. Errores de validación con el formato estándar (422).
Route::middleware('throttle:envios')->group(function () {
    Route::post('/registro', [RegistroController::class, 'store']);
    Route::patch('/registro/{token}', [RegistroController::class, 'completar'])->where('token', '[A-Za-z0-9_\-\.]+');
    Route::post('/propuestas', [BuzonController::class, 'store']);
    Route::post('/eventos/{evento}/asistencia', [AgendaController::class, 'asistencia']);
    Route::post('/titular/solicitudes', [TitularController::class, 'store']);
});

Route::post('/conversion', ConversionController::class)->middleware('throttle:60,1');
Route::get('/territorio/barrios', [TerritorioController::class, 'barrios']);
