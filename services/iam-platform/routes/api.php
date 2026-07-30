<?php

use App\Http\Controllers\Api\Internal\HandoffController;
use App\Http\Controllers\Api\Internal\SessionController;
use App\Http\Controllers\Api\Internal\VerificationController;
use App\Http\Middleware\EnsureBfrnRequest;
use App\Http\Middleware\EnsureIamInternalRequest;
use Illuminate\Support\Facades\Route;

Route::prefix('internal')
    ->middleware(EnsureBfrnRequest::class)
    ->group(function () {
        Route::post('/handoff/consume', [HandoffController::class, 'consume'])
            ->name('api.internal.handoff.consume');

        Route::post('/session/validate', [SessionController::class, 'validateSession'])
            ->name('api.internal.session.validate');
    });

Route::prefix('internal/verification')
    ->middleware(EnsureIamInternalRequest::class)
    ->group(function (): void {
        Route::post('/challenges', [
            VerificationController::class,
            'create',
        ])->name(
            'api.internal.verification.challenges.create'
        );

        Route::get('/challenges/{challengeUuid}', [
            VerificationController::class,
            'show',
        ])
            ->whereUuid('challengeUuid')
            ->name(
                'api.internal.verification.challenges.show'
            );

        Route::post(
            '/challenges/{challengeUuid}/verify',
            [
                VerificationController::class,
                'verify',
            ]
        )
            ->whereUuid('challengeUuid')
            ->name(
                'api.internal.verification.challenges.verify'
            );

        Route::post(
            '/challenges/{challengeUuid}/resend',
            [
                VerificationController::class,
                'resend',
            ]
        )
            ->whereUuid('challengeUuid')
            ->name(
                'api.internal.verification.challenges.resend'
            );

        Route::delete('/challenges/{challengeUuid}', [
            VerificationController::class,
            'revoke',
        ])
            ->whereUuid('challengeUuid')
            ->name(
                'api.internal.verification.challenges.revoke'
            );
    });
