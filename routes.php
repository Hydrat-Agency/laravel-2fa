<?php

use Illuminate\Support\Facades\Route;
use Hydrat\Laravel2FA\Controllers\TwoFactorAuthController;

/*
|--------------------------------------------------------------------------
| 2FA routes
|--------------------------------------------------------------------------
|
| Theses routes are used to show token page, refresh token, and submit it.
| You may publish assets to change the view.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('auth/2fa', [TwoFactorAuthController::class, 'index'])->name('auth.2fa.index');
    Route::post('auth/2fa', [TwoFactorAuthController::class, 'store'])->name('auth.2fa.store');
});
