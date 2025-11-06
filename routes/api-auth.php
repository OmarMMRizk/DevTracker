<?php

// use App\Http\Controllers\Auth\ApiAuthController;
// use App\Http\Controllers\RolePermission\RolePermissionController;
// use Illuminate\Support\Facades\Route;

// Public routes
// Route::post('/register', [ApiAuthController::class, 'register']);
// Route::post('/login', [ApiAuthController::class, 'login']);
// Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);    
// Route::post('/forgot-password-confirm', [ApiAuthController::class, 'forgotPasswordConfirm']);

// Authenticated routes
// Route::middleware('auth:sanctum')->group(function () {
    
//     Route::get('/user', [ApiAuthController::class, 'user']);
//     Route::post('/logout', [ApiAuthController::class, 'logout']);
    
//     // Email verification
//     Route::post('/email/verification-notification', [ApiAuthController::class, 'sendVerification'])
//         ->name('api.verification.send');
    
//     Route::get('/email/verify/{id}/{hash}', [ApiAuthController::class, 'verifyEmail'])
//         ->name('api.verification.verify');
        
    // Protected routes (verified email required)
    // Route::middleware(['verified.api'])->group(function () {
    //     Route::get('/protected-data', function () {
    //         return response()->json(['message' => 'محتوى محمي - البريد مؤكد!']);
    //     });
    // });

    // Roles & Permissions Management (Admin only)
//     Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        
//         // Roles
//         Route::get('/roles', [RolePermissionController::class, 'getRoles']);
//         Route::get('/roles/{id}', [RolePermissionController::class,'showRole']);

//         Route::post('/roles', [RolePermissionController::class, 'createRole']);

//         Route::put('/roles/{id}', [RolePermissionController::class, 'updateRole']);
//         Route::delete('/roles/{id}', [RolePermissionController::class, 'deleteRole']);
        
//         // Permissions
//         Route::get('/permissions', [RolePermissionController::class, 'getPermissions']);
//         Route::post('/permissions', [RolePermissionController::class, 'createPermission']);
//         Route::delete('/permissions/{id}', [RolePermissionController::class, 'deletePermission']);
        
//         // User Roles & Permissions
//         Route::post('/users/assign-role', [RolePermissionController::class, 'assignRole']);
//         Route::post('/users/remove-role', [RolePermissionController::class, 'removeRole']);
//         Route::post('/users/assign-permission', [RolePermissionController::class, 'assignPermission']);
//         Route::post('/users/remove-permission', [RolePermissionController::class, 'removePermission']);
//         Route::get('/users/{id}/roles-permissions', [RolePermissionController::class, 'getUserRolesPermissions']);
//     });

// });