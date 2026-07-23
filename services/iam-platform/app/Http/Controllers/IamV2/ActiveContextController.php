<?php

namespace App\Http\Controllers\IamV2;

use App\Authorization\Context\ActiveContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class ActiveContextController
{
    public function __construct(
        private readonly ActiveContextService $contexts
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $authIdentityId = $this->authIdentityId(
            $request
        );

        if ($authIdentityId === null) {
            return new JsonResponse(
                [
                    'message' =>
                        'An authenticated IAM session is required.',
                ],
                401
            );
        }

        return new JsonResponse([
            'data' => $this->contexts->available(
                $authIdentityId
            ),
            'active' => [
                'company_id' => $this->positiveInteger(
                    $request->session()->get(
                        'active_company_id'
                    )
                ),
                'business_unit_id' =>
                    $this->positiveInteger(
                        $request->session()->get(
                            'active_bu_id'
                        )
                    ),
                'system_id' => $this->positiveInteger(
                    $request->session()->get(
                        'active_system_id'
                    )
                ),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $authIdentityId = $this->authIdentityId(
            $request
        );

        if ($authIdentityId === null) {
            return new JsonResponse(
                [
                    'message' =>
                        'An authenticated IAM session is required.',
                ],
                401
            );
        }

        try {
            $companyId = $this->requiredPositiveInteger(
                $request->input('company_id'),
                'company_id'
            );

            $businessUnitId =
                $this->requiredPositiveInteger(
                    $request->input('business_unit_id'),
                    'business_unit_id'
                );

            $systemId = $this->requiredPositiveInteger(
                $request->input('system_id'),
                'system_id'
            );

            $this->contexts->switch(
                $request,
                companyId: $companyId,
                businessUnitId: $businessUnitId,
                systemId: $systemId,
            );
        } catch (
            InvalidArgumentException|RuntimeException $exception
        ) {
            return new JsonResponse(
                [
                    'message' =>
                        'The selected IAM context was rejected.',
                    'error' => $exception->getMessage(),
                ],
                422
            );
        }

        return new JsonResponse([
            'message' =>
                'Active IAM context changed successfully.',
            'active' => [
                'company_id' => $companyId,
                'business_unit_id' => $businessUnitId,
                'system_id' => $systemId,
            ],
        ]);
    }

    private function authIdentityId(
        Request $request
    ): ?int {
        return $this->positiveInteger(
            $request->session()->get(
                'auth_identity_id'
            )
        );
    }

    private function requiredPositiveInteger(
        mixed $value,
        string $field
    ): int {
        $integer = $this->positiveInteger($value);

        if ($integer === null) {
            throw new InvalidArgumentException(
                "{$field} must be a positive integer."
            );
        }

        return $integer;
    }

    private function positiveInteger(
        mixed $value
    ): ?int {
        if (
            !is_int($value)
            && !(is_string($value) && ctype_digit($value))
        ) {
            return null;
        }

        $integer = (int) $value;

        return $integer > 0
            ? $integer
            : null;
    }
}
