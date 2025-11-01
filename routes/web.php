<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/order/{orderId}', [App\Http\Controllers\OrderController::class, 'show'])->name('order.show');

Auth::routes([
    'register' => false,
    'verify' => false,
    'reset' => false,
    'login' => false,
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
