<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::get('/get_industry',[CategoryController::class, 'get_industry']);
Route::get('/get_all_category',[CategoryController::class, 'get_all_category']);
Route::post('/get_category_by_id', [CategoryController::class, 'get_category_by_id']);
Route::post('/get_product_by_id', [CategoryController::class, 'get_product_by_id']);
Route::post('/login_user', [AdminController::class, 'login_user']);


// Route::get('/get_industry', [CategoryController::class, 'get_industry'])->middleware('check.origin');
// Route::middleware('check.origin')->group(function () {
//     Route::get('/get_industry', [CategoryController::class, 'get_industry']);
// });