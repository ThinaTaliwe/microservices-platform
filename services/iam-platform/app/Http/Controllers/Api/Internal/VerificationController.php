<?php

namespace App\Http\Controllers\Api\Internal;

use App\Exceptions\VerificationChallengeException;
use App\Http\Controllers\Controller;
use App\Services\Verification\BootstrapApiLoginService;
use App\Services\Verification\VerificationChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class VerificationController extends Controller
{
    public function __construct(
        private readonly VerificationChallengeService $challenges,
        private readonly BootstrapApiLoginService $bootstrapApiLogins,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $data = $this->validated($request, [
            'login_attempt_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:120',
            ],
            'timezone' => [
                'nullable',
                'string',
                'max:100',
            ],
            'screen' => [
                'nullable',
                'string',
                'max:100',
            ],
            'platform' => [
                'nullable',
                'string',
                'max:120',
            ],
            'language' => [
                'nullable',
                'string',
                'max:80',
            ],
        ]);

        if ($data instanceof JsonResponse) {
            return $data;
        }

        $hasLoginAttempt =
            array_key_exists('login_attempt_id', $data)
            && $data['login_attempt_id'] !== null;

        $hasEmail =
            array_key_exists('email', $data)
            && is_string($data['email'])
            && trim($data['email']) !== '';

        if ($hasLoginAttempt === $hasEmail) {
            return $this->failure(
                422,
                'VERIFICATION_SOURCE_INVALID',
                'Provide exactly one of login_attempt_id or email.'
            );
        }

        try {
            $challenge = $hasEmail
                ? $this->bootstrapApiLogins->create(
                    $data,
                    $request->ip(),
                    (string) $request->userAgent()
                )
                : $this->challenges->createForLoginAttempt(
                    (int) $data['login_attempt_id']
                );

            $reused = (bool) (
                $challenge->reused_challenge ?? false
            );

            $bootstrapAdministrator = (bool) (
                $challenge->bootstrap_administrator ?? false
            );

            $otpSent = (bool) (
                $challenge->otp_sent ?? !$reused
            );

            $code = match (true) {
                $reused =>
                    'VERIFICATION_API_TOKEN_ISSUED',
                $bootstrapAdministrator =>
                    'BOOTSTRAP_API_OTP_SENT',
                default =>
                    'VERIFICATION_CHALLENGE_CREATED',
            };

            $message = match (true) {
                $reused =>
                    'API verification token issued for the '
                    . 'active challenge.',
                $bootstrapAdministrator =>
                    'Verification code generated and emailed.',
                default =>
                    'Verification challenge created.',
            };

            return $this->success(
                $reused ? 200 : 201,
                $code,
                $message,
                [
                    'login_attempt_id' =>
                        $challenge->login_attempt_id
                        ?? (
                            $hasLoginAttempt
                                ? (int) $data[
                                    'login_attempt_id'
                                ]
                                : null
                        ),
                    'challenge_id' =>
                        $challenge->challenge_uuid,
                    'challenge_reused' => $reused,
                    'bootstrap_administrator' =>
                        $bootstrapAdministrator,
                    'otp_sent' => $otpSent,
                    'api_token' =>
                        $challenge->api_token,
                    'api_token_returned_once' => true,
                    'verification_methods' => [
                        'otp',
                        'api_token',
                    ],
                    'expires_in' =>
                        $challenge->expires_in_seconds,
                ]
            );
        } catch (Throwable $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    public function show(string $challengeUuid): JsonResponse
    {
        try {
            $challenge = $this->challenges->findStatus(
                $challengeUuid
            );

            if (!$challenge) {
                return $this->failure(
                    404,
                    'VERIFICATION_CHALLENGE_NOT_FOUND',
                    'Verification challenge was not found.'
                );
            }

            return $this->success(
                200,
                'VERIFICATION_CHALLENGE_RETRIEVED',
                'Verification challenge retrieved.',
                $this->statusData($challenge)
            );
        } catch (Throwable $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    public function verify(
        Request $request,
        string $challengeUuid
    ): JsonResponse {
        $data = $this->validated($request, [
            'code' => [
                'nullable',
                'digits:6',
                'required_without:api_token',
            ],
            'api_token' => [
                'nullable',
                'string',
                'min:64',
                'max:255',
                'required_without:code',
            ],
        ]);

        if ($data instanceof JsonResponse) {
            return $data;
        }

        try {
            $verified = $this->challenges->verify(
                $challengeUuid,
                isset($data['code'])
                    ? (string) $data['code']
                    : null,
                isset($data['api_token'])
                    ? (string) $data['api_token']
                    : null
            );

            if (!$verified) {
                return $this->failure(
                    401,
                    'VERIFICATION_CREDENTIAL_REJECTED',
                    'The verification credential is invalid, '
                    . 'expired, locked, revoked, or already used.'
                );
            }

            return $this->success(
                200,
                'VERIFICATION_SUCCESS',
                'Verification completed successfully.',
                [
                    'challenge_id' =>
                        $verified->challenge_uuid,
                    'verified_via' =>
                        $verified->verified_via,
                    'authenticated' => true,
                    'bfrn_user_id' =>
                        $verified->bfrn_user_id,
                    'iam_session' => [
                        'id' =>
                            $verified->iam_session_id,
                        'token' =>
                            $verified->iam_session_token,
                        'expires_at' =>
                            $verified
                                ->iam_session_expires_at,
                    ],
                    'handoff_token' =>
                        $verified->handoff_token,
                ]
            );
        } catch (Throwable $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    public function resend(string $challengeUuid): JsonResponse
    {
        try {
            $challenge = $this->challenges->resend(
                $challengeUuid
            );

            return $this->success(
                201,
                'VERIFICATION_CHALLENGE_RESENT',
                'A replacement verification challenge was created.',
                [
                    'challenge_id' =>
                        $challenge->challenge_uuid,
                    'api_token' => $challenge->api_token,
                    'api_token_returned_once' => true,
                    'verification_methods' => [
                        'otp',
                        'api_token',
                    ],
                    'expires_in' =>
                        $challenge->expires_in_seconds,
                ]
            );
        } catch (Throwable $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    public function revoke(string $challengeUuid): JsonResponse
    {
        try {
            $challenge = $this->challenges->revoke(
                $challengeUuid
            );

            return $this->success(
                200,
                'VERIFICATION_CHALLENGE_REVOKED',
                'Verification challenge revoked.',
                [
                    'challenge_id' =>
                        $challenge->challenge_uuid,
                    'status' => $challenge->status,
                    'revoked_at' =>
                        $challenge->revoked_at,
                ]
            );
        } catch (Throwable $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    private function validated(
        Request $request,
        array $rules
    ): array|JsonResponse {
        $validator = Validator::make(
            $request->all(),
            $rules
        );

        if (!$validator->fails()) {
            return $validator->validated();
        }

        $errors = [];

        foreach (
            $validator->errors()->messages()
            as $field => $messages
        ) {
            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $field,
                    'reason' => $message,
                ];
            }
        }

        return $this->failure(
            422,
            'VALIDATION_FAILED',
            'The request data is invalid.',
            $errors
        );
    }

    private function statusData(object $challenge): array
    {
        return [
            'challenge_id' =>
                $challenge->challenge_uuid,
            'purpose' => $challenge->purpose,
            'status' => $challenge->status,
            'attempts' => $challenge->attempts,
            'max_attempts' => $challenge->max_attempts,
            'remaining_attempts' =>
                $challenge->remaining_attempts,
            'resend_count' => $challenge->resend_count,
            'max_resends' => $challenge->max_resends,
            'expires_at' => $challenge->expires_at,
            'verified_at' => $challenge->verified_at,
            'verified_via' => $challenge->verified_via,
            'consumed_at' => $challenge->consumed_at,
            'revoked_at' => $challenge->revoked_at,
            'created_at' => $challenge->created_at,
            'updated_at' => $challenge->updated_at,
        ];
    }

    private function exceptionResponse(
        Throwable $exception
    ): JsonResponse {
        if (
            $exception
            instanceof VerificationChallengeException
        ) {
            return $this->failure(
                $exception->httpStatus,
                $exception->errorCode,
                $exception->getMessage()
            );
        }

        report($exception);

        return $this->failure(
            500,
            'VERIFICATION_INTERNAL_ERROR',
            'The verification request could not be completed.'
        );
    }

    private function success(
        int $status,
        string $code,
        string $message,
        array $data
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $status)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ]);
    }

    private function failure(
        int $status,
        string $code,
        string $message,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ], $status)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ]);
    }
}
