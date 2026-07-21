<?php

namespace App\Http\Controllers\Api\IamV2;

use App\Authorization\Assignment\Http\ContextRoleAssignmentFilterFactory;
use App\Authorization\Assignment\Query\ContextRoleAssignmentQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContextRoleAssignmentController
{
    public function __construct(
        private readonly ContextRoleAssignmentQueryService $queries,
        private readonly ContextRoleAssignmentFilterFactory $filters,
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        try {
            $filter = $this->filters->fromRequest(
                $request
            );
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(
                [
                    'message' => 'Invalid assignment filters.',
                    'error' => $exception->getMessage(),
                ],
                422
            );
        }

        $assignments = $this->queries->paginate(
            $filter
        );

        return new JsonResponse([
            'data' => $assignments
                ->getCollection()
                ->map(
                    static fn ($assignment): array =>
                        $assignment->toArray()
                )
                ->values()
                ->all(),

            'meta' => [
                'current_page' =>
                    $assignments->currentPage(),
                'per_page' =>
                    $assignments->perPage(),
                'total' =>
                    $assignments->total(),
                'last_page' =>
                    $assignments->lastPage(),
            ],

            'filters' => [
                'auth_identity_id' =>
                    $filter->authIdentityId,
                'company_id' =>
                    $filter->companyId,
                'business_unit_id' =>
                    $filter->businessUnitId,
                'system_id' =>
                    $filter->systemId,
                'role' =>
                    $filter->normalizedRoleName(),
                'status' =>
                    $filter->normalizedStatus(),
            ],
        ]);
    }
}
