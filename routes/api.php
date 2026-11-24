<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerCartController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileConfigController;
use App\Http\Controllers\Api\MobileCheckoutController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\MobileCheckoutReturnController;
use App\Http\Middleware\CatalogResponse;
use App\Http\Middleware\MobileAuthResponse;
use App\Http\Middleware\MobileLocale;
use App\Http\Middleware\RequireMobileToken;
use App\Http\Resources\AuthUserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public stateless navigation bridge; the signature is not proof of payment.
Route::get('/mobile/checkout/return/{order}/{outcome}', MobileCheckoutReturnController::class)
    ->whereNumber('order')->whereIn('outcome', ['returned', 'closed'])
    ->middleware(['signed', 'throttle:60,1'])->name('mobile.checkout.return');

Route::get('/user', fn (Request $request) => response()->json((new AuthUserResource($request->user()))->resolve($request)))
    ->middleware([MobileAuthResponse::class, 'auth:sanctum']);

Route::prefix('v1/auth')->name('api.v1.auth.')->middleware(MobileAuthResponse::class)->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register'])->middleware('throttle:mobile-register')->name('register');
    Route::post('/login', [MobileAuthController::class, 'login'])->middleware('throttle:mobile-login')->name('login');
    Route::post('/social/exchange', [MobileAuthController::class, 'socialExchange'])
        ->middleware('throttle:mobile-login')->name('social.exchange');
    Route::post('/social/verify', [MobileAuthController::class, 'socialVerify'])
        ->middleware('throttle:mobile-login')->name('social.verify');
    Route::middleware(['auth:sanctum', RequireMobileToken::class, 'throttle:mobile-account'])->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me'])->name('me');
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
        Route::get('/devices', [MobileAuthController::class, 'devices'])->name('devices');
        Route::delete('/devices/{device}', [MobileAuthController::class, 'revoke'])->whereNumber('device')->name('devices.revoke');
        Route::post('/email/verification-notification', [MobileAuthController::class, 'resendVerification'])
            ->middleware('throttle:mobile-verification')->name('verification.send');
    });
});

Route::prefix('v1')->name('api.v1.customer.')->middleware([
    MobileAuthResponse::class, 'auth:sanctum', RequireMobileToken::class, 'throttle:mobile-account', MobileLocale::class,
])->group(function () {
    Route::middleware('throttle:mobile-checkout')->group(function () {
        Route::post('/checkout', [MobileCheckoutController::class, 'store'])->name('checkout.store');
        Route::post('/checkout/{order}/resume', [MobileCheckoutController::class, 'resume'])->whereNumber('order')->name('checkout.resume');
        Route::post('/checkout/{order}/refresh', [MobileCheckoutController::class, 'refresh'])->whereNumber('order')->name('checkout.refresh');
        Route::post('/checkout/{order}/cancel', [MobileCheckoutController::class, 'cancel'])->whereNumber('order')->name('checkout.cancel');
    });
    Route::get('/cart', [CustomerCartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [CustomerCartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items/{item}', [CustomerCartController::class, 'update'])->whereNumber('item')->name('cart.update');
    Route::delete('/cart/items/{item}', [CustomerCartController::class, 'remove'])->whereNumber('item')->name('cart.remove');
    Route::delete('/cart', [CustomerCartController::class, 'clear'])->name('cart.clear');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('/orders/{order}/delivery', [CustomerOrderController::class, 'delivery'])->whereNumber('order')->name('orders.delivery');
});

Route::middleware([CatalogResponse::class, 'throttle:catalog'])->group(function () {
    Route::prefix('v1')->name('api.v1.')->group(function () {
        Route::get('/openapi.json', fn () => response()->file(base_path('docs/catalog-api-openapi.json'), ['Content-Type' => 'application/json']))->name('openapi');
        Route::get('/mobile/config', MobileConfigController::class)->name('mobile.config');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
        Route::get('/shops/{slug}', [ShopController::class, 'show'])->name('shops.show');
        Route::get('/shops/{slug}/listings', [ListingController::class, 'forShop'])->name('shops.listings');
        Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
        Route::get('/listings/{slug}', [ListingController::class, 'show'])->name('listings.show');
    });
    // Compatibility routes use the same visibility, safe serializers, pagination and shared rate budget.
    Route::get('/shops', [ShopController::class, 'index']);
    Route::get('/shops/{slug}', [ShopController::class, 'show']);
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{slug}', [ListingController::class, 'show']);
});

// MOBILE_HOME_API_ROUTES
Route::middleware([CatalogResponse::class, 'throttle:catalog'])
    ->prefix('v1/mobile/home')
    ->name('api.v1.mobile.home.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\MobileHomeController::class, 'show'])
            ->name('show');
    });

Route::prefix('v1/admin/mobile/home')
    ->name('api.v1.admin.mobile.home.')
    ->middleware([
        'auth:sanctum',
        'role:superadministrator|administrator',
        'throttle:60,1',
    ])
    ->group(function () {
        Route::get('/preview', [\App\Http\Controllers\Api\MobileHomeController::class, 'preview'])
            ->name('preview');

        Route::put('/draft', [\App\Http\Controllers\Api\MobileHomeController::class, 'draft'])
            ->name('draft');

        Route::post('/publish', [\App\Http\Controllers\Api\MobileHomeController::class, 'publish'])
            ->name('publish');

        Route::get('/revisions', [\App\Http\Controllers\Api\MobileHomeController::class, 'revisions'])
            ->name('revisions');

        Route::post('/restore/{revision}', [\App\Http\Controllers\Api\MobileHomeController::class, 'restore'])
            ->whereNumber('revision')
            ->name('restore');
    });
