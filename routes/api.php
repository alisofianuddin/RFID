<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RfidController;

// RFID Scan (dari Python reader)
Route::post('/rfid/scan', [RfidController::class, 'scan']);

// Logs
Route::get('/rfid/logs', [RfidController::class, 'logs']);

// Stats
Route::get('/rfid/stats', [RfidController::class, 'stats']);

// Register Scan Reader
Route::post('/rfid/register-scan', [RfidController::class, 'registerScan']);
Route::get('/rfid/register-scan', [RfidController::class, 'getRegisterScan']);
Route::delete('/rfid/register-scan', [RfidController::class, 'clearRegisterScan']);

// Card Management
Route::get('/rfid/cards', [RfidController::class, 'cardIndex']);
Route::post('/rfid/cards', [RfidController::class, 'cardStore']);
Route::put('/rfid/cards/{card}', [RfidController::class, 'cardUpdate']);
Route::delete('/rfid/cards/{card}', [RfidController::class, 'cardDestroy']);

// Reader Control (dashboard → Laravel → Python via cache queue)
Route::get('/rfid/reader/status', [RfidController::class, 'readerStatus']);
Route::get('/rfid/reader/info', [RfidController::class, 'readerInfo']);
Route::post('/rfid/reader/power', [RfidController::class, 'setReaderPower']);

// Reader Command Queue (Python register_reader.py polling)
Route::get('/rfid/reader/command', [RfidController::class, 'getReaderCommand']);
Route::get('/rfid/reader/command-result/{cmdId}', [RfidController::class, 'getCommandResult']);
Route::post('/rfid/reader/command-result', [RfidController::class, 'postReaderResult']);
Route::post('/rfid/reader/heartbeat', [RfidController::class, 'readerHeartbeat']);

// Scan Reader Command Queue (Python main.py polling)
Route::get('/rfid/scan-reader/status', [RfidController::class, 'scanReaderStatus']);
Route::post('/rfid/scan-reader/power', [RfidController::class, 'setScanReaderPower']);
Route::get('/rfid/scan-reader/command', [RfidController::class, 'getScanReaderCommand']);
Route::get('/rfid/scan-reader/command-result/{cmdId}', [RfidController::class, 'getScanCommandResult']);
Route::post('/rfid/scan-reader/command-result', [RfidController::class, 'postScanReaderResult']);
Route::post('/rfid/scan-reader/heartbeat', [RfidController::class, 'scanReaderHeartbeat']);
Route::get('/rfid/scan-reader/config', [RfidController::class, 'getScanReaderConfig']);
Route::post('/rfid/scan-reader/config', [RfidController::class, 'saveScanReaderConfig']);

