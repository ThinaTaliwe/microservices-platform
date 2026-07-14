<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Internal\BfrnInternalAuthController;
use App\Http\Middleware\EnsureIamPlatformRequest;

Route::prefix('bfrn/internal/auth')
    ->middleware(EnsureIamPlatformRequest::class)
    ->name('bfrn.api.internal.auth.')
    ->group(function () {
        Route::post('/user/find', [BfrnInternalAuthController::class, 'findUser'])->name('user.find');
        Route::get('/business-units', [BfrnInternalAuthController::class, 'businessUnits'])->name('business-units');
        Route::post('/user/provision', [BfrnInternalAuthController::class, 'provisionUser'])->name('user.provision');
        Route::post('/user/business-unit', [BfrnInternalAuthController::class, 'assignBusinessUnit'])->name('user.business-unit');
    });

