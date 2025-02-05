<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
// Route::get('/get_industry',[CategoryController::class, 'get_industry']);
// Route::get('/get_industry', [CategoryController::class, 'get_industry'])->middleware('check.origin');
Route::middleware('check.origin')->group(function () {
    Route::get('/get_industry', [CategoryController::class, 'get_industry']);
});