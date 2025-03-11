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
Route::post('/toggleproduct', [AdminController::class, 'toggleproduct']);
Route::post('/create_product', [AdminController::class, 'create_product']);
Route::post('/delete_product', [AdminController::class, 'delete_product']);
Route::get('/get_category_product', [AdminController::class, 'get_category_product']);

Route::get('/get_all_forms', [AdminController::class, 'get_all_forms']);
Route::post('/changeformstatus', [AdminController::class, 'changeformstatus']);

Route::post('/create_update_blog', [AdminController::class, 'create_update_blog']);
Route::get('/getadminBlogs', [AdminController::class, 'getadminBlogs']);
Route::get('/getBlogs', [AdminController::class, 'getBlogs']);
Route::post('/getBlogById', [AdminController::class, 'getBlogById']);
Route::post('/deleteBlog', [AdminController::class, 'deleteBlog']);
Route::post('/toggleblog', [AdminController::class, 'toggleblog']);

Route::post('/create_update_portfolio', [AdminController::class, 'create_update_portfolio']);
Route::get('/getPortfolios', [AdminController::class, 'getPortfolios']);
Route::post('/deletePortfolio', [AdminController::class, 'deletePortfolio']);

Route::post('/deleteimages', [AdminController::class, 'deleteimages']);

// Route::get('/get_industry', [CategoryController::class, 'get_industry'])->middleware('check.origin');
// Route::middleware('check.origin')->group(function () {
//     Route::get('/get_industry', [CategoryController::class, 'get_industry']);
// });