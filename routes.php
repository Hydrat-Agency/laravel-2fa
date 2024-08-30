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
    Route::get('token', [TwoFactorAuthController::class, 'index'])->name('token.index');
    Route::post('token', [TwoFactorAuthController::class, 'store'])->name('token.store');
});
