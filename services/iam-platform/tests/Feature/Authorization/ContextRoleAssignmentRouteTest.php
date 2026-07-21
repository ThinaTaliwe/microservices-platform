<?php

namespace Tests\Feature\Authorization;

use App\Authorization\Assignment\Query\ContextRoleAssignmentQueryService;
use App\Authorization\Context\AccessContext;
use App\Authorization\Contracts\ComponentReferenceResolver;
use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Contracts\ContextRoleAssignmentQueryRepository;
use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Resolver\ContextResolution;
use App\Authorization\Snapshot\AccessSnapshot;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Tests\Unit\Authorization\Fakes\FakeComponentReferenceResolver;
use Tests\Unit\Authorization\Fakes\FakeContextAccessRepository;
use Tests\Unit\Authorization\Fakes\FakeContextRoleAssignmentQueryRepository;
use Tests\Unit\Authorization\Fakes\FakeRequestContextResolver;

class ContextRoleAssignmentRouteTest extends TestCase
{
    public function test_unresolved_context_returns_json_403(): void
    {
        $this->app->instance(
            ComponentReferenceResolver::class,
            new FakeComponentReferenceResolver(8)
        );

        $this->app->instance(
            RequestContextResolver::class,
            new FakeRequestContextResolver(
                ContextResolution::failure(
                    'missing_auth_identity'
                )
            )
        );

        $response = $this->getJson(
            '/iam-v2/role-assignments'
        );

        $response->assertForbidden();
    }

    public function test_authorized_request_returns_paginated_json(): void
    {
        $context = new AccessContext(
            authIdentityId: 1,
            companyId: 1,
            businessUnitId: 1,
            systemId: 1,
            componentId: 8,
        );

        $this->app->instance(
            ComponentReferenceResolver::class,
            new FakeComponentReferenceResolver(8)
        );

        $this->app->instance(
            RequestContextResolver::class,
            new FakeRequestContextResolver(
                ContextResolution::success($context)
            )
        );

        $this->app->instance(
            ContextAccessRepository::class,
            new FakeContextAccessRepository(
                new AccessSnapshot(
                    companyAccess: true,
                    businessUnitAccess: true,
                    systemAccess: true,
                    roles: [],
                    permissions: [
                        'iam.context.read',
                    ],
                    overrides: [],
                )
            )
        );

        $paginator = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 25,
            currentPage: 1,
        );

        $queryRepository =
            new FakeContextRoleAssignmentQueryRepository(
                $paginator
            );

        $this->app->instance(
            ContextRoleAssignmentQueryRepository::class,
            $queryRepository
        );

        $this->app->instance(
            ContextRoleAssignmentQueryService::class,
            new ContextRoleAssignmentQueryService(
                $queryRepository
            )
        );

        $response = $this->getJson(
            '/iam-v2/role-assignments'
        );

        $response
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.current_page', 1);
    }
}
