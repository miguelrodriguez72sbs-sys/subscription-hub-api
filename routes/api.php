<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\SubscriptionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('membership-plans', MembershipPlanController::class);

Route::apiResource('subscriptions', SubscriptionController::class);