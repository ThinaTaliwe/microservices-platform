<?php

use App\Http\Controllers\Api\IamV2\ContextRoleAssignmentController;
use App\Http\Controllers\Api\IamV2\ContextRoleAssignmentCatalogueController;
use App\Http\Controllers\IamV2\ActiveContextController;
use App\Http\Controllers\IamV2\ContextRoleAssignmentPageController;
use App\Http\Middleware\EnsureContextPermission;
use App\Http\Controllers\AuthGateway\LoginController;
use App\Http\Controllers\AuthGateway\OtpController;
use App\Http\Controllers\AuthGateway\SupervisorController;
use App\Http\Controllers\ApiTest\VerificationApiTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'show'])->name('login.show');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'submit'])->name('login.submit');




Route::get('/otp/verify', [OtpController::class, 'show'])
    ->name('otp.verify');

Route::post('/otp/verify', [OtpController::class, 'verify'])
    ->name('otp.verify.submit');

Route::get('/supervisor/email/{id}/approve', [SupervisorController::class, 'approveFromEmail'])
    ->name('supervisor.email.approve');

Route::get('/supervisor/email/{id}/block', [SupervisorController::class, 'blockFromEmail'])
    ->name('supervisor.email.block');

Route::get('/supervisor', [SupervisorController::class, 'index'])
    ->name('supervisor.index');


Route::post('/supervisor/{id}/approve', [SupervisorController::class, 'approve'])
    ->name('supervisor.approve');

Route::post('/supervisor/{id}/block', [SupervisorController::class, 'block'])
    ->name('supervisor.block');

Route::get(
    '/iam-v2/active-contexts',
    [ActiveContextController::class, 'index']
)->name('iam-v2.active-contexts.index');

Route::post(
    '/iam-v2/active-context',
    [ActiveContextController::class, 'update']
)->name('iam-v2.active-contexts.update');


Route::get(
    '/iam-v2/administration/role-assignments',
    ContextRoleAssignmentPageController::class
)
    ->middleware(
        EnsureContextPermission::class
        . ':iam.context.read,iam-contexts'
    )
    ->name('iam-v2.role-assignments.page');


Route::get(
    '/iam-v2/role-assignments',
    [ContextRoleAssignmentController::class, 'index']
)
    ->middleware(
        EnsureContextPermission::class
        . ':iam.context.read,iam-contexts'
    )
    ->name('iam-v2.role-assignments.index');

Route::post(
    '/iam-v2/role-assignments',
    [ContextRoleAssignmentController::class, 'store']
)
    ->middleware(
        EnsureContextPermission::class
        . ':iam.context.manage,iam-contexts'
    )
    ->name('iam-v2.role-assignments.store');

Route::get(
    '/iam-v2/role-assignment-catalogue',
    ContextRoleAssignmentCatalogueController::class
)
    ->middleware(
        EnsureContextPermission::class
        . ':iam.context.manage,iam-contexts'
    )
    ->name('iam-v2.role-assignments.catalogue');

if (app()->environment('local')) {
    Route::prefix('/api-test/verification')
        ->name('api-test.verification.')
        ->middleware('throttle:30,1')
        ->group(function (): void {
            Route::view('/', 'api-test.verification')
                ->name('page');

            Route::post(
                '/generate',
                [VerificationApiTestController::class, 'generate']
            )->name('generate');

            Route::get(
                '/{challengeUuid}',
                [VerificationApiTestController::class, 'show']
            )->whereUuid('challengeUuid')->name('show');

            Route::post(
                '/{challengeUuid}/verify-otp',
                [VerificationApiTestController::class, 'verifyOtp']
            )->whereUuid('challengeUuid')->name('verify-otp');

            Route::post(
                '/{challengeUuid}/verify-token',
                [VerificationApiTestController::class, 'verifyToken']
            )->whereUuid('challengeUuid')->name('verify-token');

            Route::post(
                '/{challengeUuid}/resend',
                [VerificationApiTestController::class, 'resend']
            )->whereUuid('challengeUuid')->name('resend');

            Route::delete(
                '/{challengeUuid}',
                [VerificationApiTestController::class, 'revoke']
            )->whereUuid('challengeUuid')->name('revoke');
        });
}
