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
Route::get('/admin_get_categories', [AdminController::class, 'admin_get_categories']);
Route::get('/admin_get_products', [AdminController::class, 'admin_get_products']);
Route::post('/create_category', [AdminController::class, 'create_category']);
Route::post('/saved_image', [AdminController::class, 'saved_image']);
Route::post('/delete_category', [AdminController::class, 'delete_category']);
Route::post('/toggleCategory', [AdminController::class, 'toggleCategory']);
Route::post('/toggleproduct', [AdminController::class, 'toggleCategory']);
Route::post('/create_product', [AdminController::class, 'create_product']);
Route::post('/delete_product', [AdminController::class, 'delete_product']);



// Route::get('/get_industry', [CategoryController::class, 'get_industry'])->middleware('check.origin');
// Route::middleware('check.origin')->group(function () {
//     Route::get('/get_industry', [CategoryController::class, 'get_industry']);
// });