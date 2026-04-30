<?php

use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for invoice management
Route::middleware('throttle:60,1')->post('/invoice/push', [InvoiceController::class, 'pushInvoice']);
Route::middleware('throttle:60,1')->post('/invoice/validateTin', [InvoiceController::class, 'validateTin']);
