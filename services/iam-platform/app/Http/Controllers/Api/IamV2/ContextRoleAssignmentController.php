<?php

namespace App\Http\Controllers\Api\IamV2;

use App\Authorization\Assignment\ContextRoleAssignmentException;
use App\Authorization\Assignment\ContextRoleAssignmentService;
use App\Authorization\Assignment\Http\ContextRoleAssignmentFactory;
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
        private readonly ContextRoleAssignmentService $assignments,
        private readonly ContextRoleAssignmentFactory $assignmentFactory,
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

    public function store(
        Request $request
    ): JsonResponse {
        try {
            $assignment =
                $this->assignmentFactory->fromRequest(
                    $request
                );

            $this->assignments->assign(
                $assignment
            );
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(
                [
                    'message' =>
                        'Invalid role assignment.',
                    'error' =>
                        $exception->getMessage(),
                ],
                422
            );
        } catch (
            ContextRoleAssignmentException $exception
        ) {
            return new JsonResponse(
                [
                    'message' =>
                        'Role assignment was rejected.',
                    'error' =>
                        $exception->getMessage(),
                ],
                422
            );
        }

        return new JsonResponse(
            [
                'message' =>
                    'Role assignment saved successfully.',
                'assignment' => [
                    'auth_identity_id' =>
                        $assignment->authIdentityId,
                    'role' =>
                        $assignment->normalizedRoleName(),
                    'company_id' =>
                        $assignment->companyId,
                    'business_unit_id' =>
                        $assignment->businessUnitId,
                    'system_id' =>
                        $assignment->systemId,
                    'valid_from' =>
                        $assignment->validFrom?->format(
                            'Y-m-d H:i:s'
                        ),
                    'valid_until' =>
                        $assignment->validUntil?->format(
                            'Y-m-d H:i:s'
                        ),
                ],
            ],
            201
        );
    }

}
