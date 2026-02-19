<?php

use App\Modules\Ai\Controllers\AiChatController;
use App\Modules\Ai\Controllers\ConversationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| AI Module Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by AiModuleServiceProvider and protected by
| the module:ai middleware to ensure the AI module is active.
|
*/

Route::middleware(['web', 'auth', 'verified', 'company', 'module:ai'])
    ->prefix('ai')
    ->name('ai.')
    ->group(function () {
        // Main AI page
        Route::get('/', fn () => Inertia::render('Tenant/Ai/Index'))->name('index');

        // Chat endpoints
        Route::post('/chat', [AiChatController::class, 'chat'])->name('chat');
        Route::post('/chat/stream', [AiChatController::class, 'stream'])->name('chat.stream');

        // Conversations API
        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::patch('/conversations/{id}', [ConversationController::class, 'update'])->name('conversations.update');
        Route::delete('/conversations/{id}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
    });
