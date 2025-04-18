<?php

declare(strict_types=1);

use Arrowtide\Gaia\Http\Controllers\CartController;
use Arrowtide\Gaia\Http\Controllers\WishListController;
use Arrowtide\Gaia\Http\Middleware\CartManagement;
use Illuminate\Support\Facades\Route;

Route::name('gaia.')->group(function () {
    Route::group(['prefix' => 'account', 'middleware' => ['auth']], function () {
        Route::get('wishlists', [WishListController::class, 'index'])->name('index');
        Route::post('wishlist/create', [WishListController::class, 'store'])->name('store');
        Route::post('wishlist/update/', [WishListController::class, 'update'])->name('update');
        Route::post('wishlist/manage/', [WishListController::class, 'manageWishlistItems'])->name('manage');
        Route::post('wishlist/removeItem/', [WishListController::class, 'removeItem'])->name('removeItem');
        Route::patch('wishlist/rename/{id}', [WishListController::class, 'rename'])->name('rename');
        Route::delete('wishlist/destroy/{id}', [WishListController::class, 'destroy'])->name('destroy');
    });

    // Cart Management Routes - Assuming no cart exists
    Route::post('/cart/create', [CartController::class, 'create'])->name('cart.create');
    Route::post('/cart/create/empty', [CartController::class, 'createEmptyCart'])->name('cart.create.empty');

    // Cart Management Routes - Middleware will apply a fresh cart if one does not already exist.
    Route::middleware([CartManagement::class])->group(function () {
        Route::prefix('cart')->group(function () {
            Route::get('/get', [CartController::class, 'index'])->name('cart.index');
            Route::post('/add', [CartController::class, 'addToCart'])->name('cart.add');
            Route::post('/update', [CartController::class, 'updateCartLine'])->name('cart.update');
            Route::get('/checkout/url', [CartController::class, 'checkoutURL'])->name('cart.checkout-url');
            Route::post('/discount', [CartController::class, 'updateCartDiscountCodes'])->name('cart.discount');
            Route::post('/attributes', [CartController::class, 'updateCartAttributes'])->name('cart.attributes');
            Route::post('/note', [CartController::class, 'updateCartNote'])->name('cart.note');
            Route::delete('/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
            Route::delete('/delete', [CartController::class, 'deleteCart'])->name('cart.delete');
            Route::post('/update/identity', [CartController::class, 'updateCartBuyerIdentity'])->name('update.identity');
        });
    });
});
