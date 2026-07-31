<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('membership-plans', [MembershipPlanController::class, 'index']);
Route::get('membership-plans/{id}', [MembershipPlanController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('subscriptions', SubscriptionController::class);

    Route::apiResource('invoices', InvoiceController::class)
        ->only(['index', 'show']);

    Route::apiResource('payments', PaymentController::class)
        ->only(['index', 'show', 'store']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('membership-plans', MembershipPlanController::class)
            ->only(['store', 'update', 'destroy']);

        Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus']);
    });
});
