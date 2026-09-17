<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\WebsiteController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\SubscriptionController;

Route::prefix('v1')->group(function () {

    // Websites
    Route::get('/websites', [WebsiteController::class, 'index']);

    // Posts
    Route::post('/websites/{website}/posts', [PostController::class, 'store']);
    Route::get('/websites/{website}/posts', [PostController::class, 'index']);
    Route::get('/websites/{website}/posts/{post}', [PostController::class, 'getPost']);

    // Subscriptions
    Route::post('/websites/{website}/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::delete('/websites/{website}/unsubscribe', [SubscriptionController::class, 'unsubscribe']);
    Route::get('/websites/{website}/subscribers', [SubscriptionController::class, 'subscribers']);
});
