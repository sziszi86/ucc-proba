<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HelpdeskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-mfa', [AuthController::class, 'verifyMfa']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('enable-mfa', [AuthController::class, 'enableMfa']);
        Route::post('confirm-mfa', [AuthController::class, 'confirmMfa']);
        Route::post('disable-mfa', [AuthController::class, 'disableMfa']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('events', EventController::class);

    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{id}', [ChatController::class, 'show']);
        Route::post('/{id}/messages', [ChatController::class, 'sendMessage']);
        Route::delete('/{id}', [ChatController::class, 'destroy']);
    });

    Route::prefix('helpdesk')->group(function () {
        Route::get('chats', [HelpdeskController::class, 'getChats']);
        Route::get('chats/{id}', [HelpdeskController::class, 'getChat']);
        Route::post('chats/{id}/assign', [HelpdeskController::class, 'assignChat']);
        Route::post('chats/{id}/messages', [HelpdeskController::class, 'sendMessage']);
        Route::post('chats/{id}/close', [HelpdeskController::class, 'closeChat']);
    });
});
