<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\Http\ContextRoleAssignmentFilterFactory;
use App\Authorization\Assignment\Query\ContextRoleAssignmentQueryService;
use App\Authorization\Assignment\Query\ContextRoleAssignmentView;
use App\Http\Controllers\Api\IamV2\ContextRoleAssignmentController;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeContextRoleAssignmentQueryRepository;

class ContextRoleAssignmentControllerTest extends TestCase
{
    public function test_it_returns_paginated_json(): void
    {
        $view = new ContextRoleAssignmentView(
            id: 1,
            authIdentityId: 10,
            roleId: 11,
            roleName: 'supervisor',
            companyId: 20,
            companyName: 'Company',
            businessUnitId: 30,
            businessUnitName: 'BU',
            systemId: 40,
            systemName: 'IAM Platform',
            status: 'active',
            validFrom: null,
            validUntil: null,
            createdAt: '2026-07-17 08:00:00',
            updatedAt: '2026-07-17 08:00:00',
        );

        $paginator = new LengthAwarePaginator(
            items: [$view],
            total: 1,
            perPage: 25,
            currentPage: 1,
        );

        $repository =
            new FakeContextRoleAssignmentQueryRepository(
                $paginator
            );

        $controller =
            new ContextRoleAssignmentController(
                queries:
                    new ContextRoleAssignmentQueryService(
                        $repository
                    ),
                filters:
                    new ContextRoleAssignmentFilterFactory(),
            );

        $response = $controller->index(
            Request::create(
                '/assignments',
                'GET',
                [
                    'auth_identity_id' => '10',
                    'status' => 'active',
                ]
            )
        );

        $payload = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertSame(
            'supervisor',
            $payload['data'][0]['role_name']
        );
        $this->assertSame(
            1,
            $payload['meta']['total']
        );
        $this->assertSame(
            10,
            $payload['filters']['auth_identity_id']
        );
    }

    public function test_invalid_filters_return_422(): void
    {
        $paginator = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 25,
            currentPage: 1,
        );

        $controller =
            new ContextRoleAssignmentController(
                queries:
                    new ContextRoleAssignmentQueryService(
                        new FakeContextRoleAssignmentQueryRepository(
                            $paginator
                        )
                    ),
                filters:
                    new ContextRoleAssignmentFilterFactory(),
            );

        $response = $controller->index(
            Request::create(
                '/assignments',
                'GET',
                [
                    'per_page' => '500',
                ]
            )
        );

        $this->assertSame(422, $response->status());
    }
}
