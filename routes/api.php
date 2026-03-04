<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidController;

// RFID Scan (dari Python reader)
Route::post('/rfid/scan', [RfidController::class, 'scan']);

// Logs
Route::get('/rfid/logs', [RfidController::class, 'logs']);

// Stats
Route::get('/rfid/stats', [RfidController::class, 'stats']);

// Card Management
Route::get('/rfid/cards', [RfidController::class, 'cardIndex']);
Route::post('/rfid/cards', [RfidController::class, 'cardStore']);
Route::put('/rfid/cards/{card}', [RfidController::class, 'cardUpdate']);
Route::delete('/rfid/cards/{card}', [RfidController::class, 'cardDestroy']);

// Reader Control
Route::get('/rfid/reader/status', [RfidController::class, 'readerStatus']);
Route::get('/rfid/reader/info', [RfidController::class, 'readerInfo']);
Route::post('/rfid/reader/power', [RfidController::class, 'setReaderPower']);
