<?php

use App\Http\Controllers\Api\v1\LoginController;
use App\Http\Controllers\Api\v1\ProfileController;
use App\Http\Controllers\Api\v1\RegistrationController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(RegistrationController::class)->group(function () {
            Route::post('/register', 'register')->name('auth.register');
        });
        Route::controller(LoginController::class)->group(function () {
            Route::post('/login', 'login')->name('auth.login');
        });
        Route::controller(ProfileController::class)->group(function () {
            Route::patch('/directUpdate', 'directUpdate')->name('profile.directUpdate');
            Route::get('/directProfile', 'directProfile')->name('profile.directProfile');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('profile')->group(function () {
            Route::controller(ProfileController::class)->group(function () {
                Route::patch('/', 'profile')->name('profile.update');
            });
        });
    });
});

