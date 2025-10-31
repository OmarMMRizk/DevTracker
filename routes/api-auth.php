<?php

use App\Http\Controllers\Auth\ApiAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

// methods: api-auth.php
// Public routes
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);    
Route::post('/forgot-password-confirm', [ApiAuthController::class,'forgotPasswordConfirm']);



// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    
    // Email verification
    Route::post('/email/verification-notification', [ApiAuthController::class, 'sendVerification'])
        ->name('api.verification.send');
    
    Route::get('/email/verify/{id}/{hash}', [ApiAuthController::class, 'verifyEmail'])
        ->name('api.verification.verify');
        
    // Protected routes (verified email required)
    Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {
        Route::get('/protected-data', function () {
            return response()->json(['message' => 'محتوى محمي - البريد مؤكد!']);
        });
    });



});