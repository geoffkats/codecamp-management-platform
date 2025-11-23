<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CodeExecutionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Code execution endpoints (protected by auth)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/execute/python', [CodeExecutionController::class, 'executePython']);
    Route::post('/execute/javascript', [CodeExecutionController::class, 'executeJavaScript']);
});
