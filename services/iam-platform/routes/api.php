<?php

use App\Http\Controllers\Api\Internal\HandoffController;
use App\Http\Controllers\Api\Internal\SessionController;
use App\Http\Middleware\EnsureBfrnRequest;
use Illuminate\Support\Facades\Route;

Route::prefix('internal')
    ->middleware(EnsureBfrnRequest::class)
    ->group(function () {
        Route::post('/handoff/consume', [HandoffController::class, 'consume'])
            ->name('api.internal.handoff.consume');

        Route::post('/session/validate', [SessionController::class, 'validateSession'])
            ->name('api.internal.session.validate');
    });
