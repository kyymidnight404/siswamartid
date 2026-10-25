<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SellerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::post('/products/{product}/whatsapp', [ProductController::class, 'trackWhatsapp'])->middleware('throttle:30,1');
Route::post('/products/{product}/view', [ProductController::class, 'trackView'])->middleware('throttle:60,1');

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

// Sellers - Public
Route::get('/sellers', [SellerController::class, 'index']);
Route::get('/sellers/{seller}', [SellerController::class, 'show']);

// Reviews - Public
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

// Submit review - Public / Guest
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:10,1');


/*
|--------------------------------------------------------------------------
| Authenticated API
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    // Seller Dashboard & Store Status
    Route::get('/seller/dashboard', [SellerController::class, 'dashboard']);
    Route::patch('/seller/store-status', [SellerController::class, 'toggleStoreStatus']);
});