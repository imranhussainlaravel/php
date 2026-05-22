<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('no.cache')->group(function () {
    Route::get('/product/{slug}', fn () => response('Gone', 410));
    Route::get('/category/{slug}', fn () => response('Gone', 410));
    Route::get('/blog/{slug}', fn () => response('Gone', 410));
    Route::get('/search/{slug?}', fn () => response('Gone', 410))->where('slug', '.*');
});
