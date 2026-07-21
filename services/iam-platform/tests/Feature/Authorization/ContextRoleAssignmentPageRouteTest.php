<?php

namespace Tests\Feature\Authorization;

use App\Authorization\Context\AccessContext;
use App\Authorization\Contracts\ComponentReferenceResolver;
use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Resolver\ContextResolution;
use App\Authorization\Snapshot\AccessSnapshot;
use Tests\TestCase;
use Tests\Unit\Authorization\Fakes\FakeComponentReferenceResolver;
use Tests\Unit\Authorization\Fakes\FakeContextAccessRepository;
use Tests\Unit\Authorization\Fakes\FakeRequestContextResolver;

class ContextRoleAssignmentPageRouteTest extends TestCase
{
    public function test_unresolved_context_returns_403(): void
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

        $this
            ->get(
                '/iam-v2/administration/'
                . 'role-assignments'
            )
            ->assertForbidden();
    }

    public function test_authorized_request_returns_page(): void
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

        $this
            ->get(
                '/iam-v2/administration/'
                . 'role-assignments'
            )
            ->assertOk()
            ->assertViewIs(
                'iam-v2.role-assignments.index'
            )
            ->assertSee(
                'Contextual Role Assignments'
            )
            ->assertViewHas(
                'assignmentEndpoint',
                route(
                    'iam-v2.role-assignments.index'
                )
            );
    }
}
