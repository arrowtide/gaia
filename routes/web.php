<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public auth pages (accessible without login)
Route::prefix('account')->group(function () {
    Route::statamic('login', 'customer/auth/login/index')->name('login');
    Route::statamic('register', 'customer/auth/register/index');
    Route::statamic('recover', 'customer/auth/recover/index');
    Route::statamic('recover/reset', 'customer/auth/reset-password/index');
});

// Authenticated account pages
Route::prefix('account')->middleware(['auth'])->group(function () {
    Route::statamic('', 'customer/profile/profile/index');
    Route::statamic('settings', 'customer/profile/settings/index');
    Route::statamic('orders', 'customer/profile/orders/index');
    Route::statamic('orders/{order_id}', 'customer/profile/orders/show');
});

// Wishlist route, only if enabled
if (config('gaia.wishlists.enabled')) {
    Route::prefix('account')->middleware(['auth'])->group(function () {
        Route::statamic('saved', 'features/wishlist/customer/profile/saved/index');
    });
}

// Playground routes
if (config('gaia.playground.enabled') === true ||
    (config('gaia.playground.enabled') === 'auto' && App::environment('local'))) {
    Route::statamic(config('gaia.playground.route'), 'dev/playground/index');
}
