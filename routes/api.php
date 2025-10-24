<?php

use App\Http\Controllers\Api\v1\RegistrationController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(RegistrationController::class)->group(function () {
            Route::post('/register', 'register')->name('auth.register');
        });
    });
});

