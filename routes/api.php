<?php

use App\Http\Controllers\Api\v1\BookingController;
use App\Http\Controllers\Api\v1\LoginController;
use App\Http\Controllers\Api\v1\PaymentController;
use App\Http\Controllers\Api\v1\ProfileController;
use App\Http\Controllers\Api\v1\RegistrationController;
use App\Http\Controllers\Api\v1\SearchController;
use App\Http\Controllers\Api\v1\ServiceController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(RegistrationController::class)->group(function () {
            Route::post('/register', 'register')->name('auth.register');
        });
        Route::controller(LoginController::class)->group(function () {
            Route::post('/login', 'login')->name('auth.login');
            Route::post('/logout', 'logout')->name('auth.logout');
        });
        Route::controller(ProfileController::class)->group(function () {
            Route::patch('/directUpdate', 'directUpdate')->name('profile.directUpdate');
            Route::get('/directProfile', 'directProfile')->name('profile.directProfile');
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('profile')->group(function () {
            Route::controller(ProfileController::class)->group(function () {
                Route::post('/', 'profile')->name('profile.update');
            });
        });
    });

    Route::prefix('services')->group(function () {
        Route::controller(ServiceController::class)->group(function () {
            Route::get('/', 'list')->name('services.list');
        });
    });

    Route::prefix('search')->group(function () {
        Route::controller(SearchController::class)->group(function () {
            Route::get('/place', 'place')->name('search.place');
            Route::post('/', 'price')->name('search.price');
        });
    });

    Route::prefix('booking')->group(function () {
        Route::controller(BookingController::class)->group(function () {
            Route::post('/', 'booking')->name('booking');
        });
    });

    Route::prefix('payment')->group(function () {
        Route::controller(PaymentController::class)->group(function () {
            Route::get('/success', 'success')->name('success');
            Route::get('/failed', 'failed')->name('failed');
        });
    });
});

