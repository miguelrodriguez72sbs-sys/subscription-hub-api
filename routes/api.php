<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('membership-plans', [MembershipPlanController::class, 'index']);
Route::get('membership-plans/{id}', [MembershipPlanController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::apiResource('subscriptions', SubscriptionController::class);

    Route::apiResource('invoices', InvoiceController::class)
        ->only(['index', 'show']);

    Route::apiResource('payments', PaymentController::class)
        ->only(['index', 'show', 'store']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('membership-plans', MembershipPlanController::class)
            ->only(['store', 'update', 'destroy']);

        Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus']);

        Route::get('users', [UserController::class, 'index']);
        Route::patch('users/{id}/role', [UserController::class, 'updateRole']);

        Route::get('reports', [ReportController::class, 'index']);
        Route::get('reports/revenue', [ReportController::class, 'revenue']);
        Route::get('reports/subscriptions', [ReportController::class, 'subscriptions']);
        Route::get('reports/invoices', [ReportController::class, 'invoices']);
    });
});
