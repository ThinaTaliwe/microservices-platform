<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\ContextRoleAssignment;
use App\Authorization\Assignment\ContextRoleAssignmentService;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeAuthorizationCacheInvalidator;
use Tests\Unit\Authorization\Fakes\FakeContextRoleAssignmentRepository;

class ContextRoleAssignmentTest extends TestCase
{
    public function test_assignment_normalizes_role_name(): void
    {
        $assignment = new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: ' Supervisor ',
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );

        $this->assertSame(
            'supervisor',
            $assignment->normalizedRoleName()
        );
    }

    public function test_business_unit_requires_company(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: 'supervisor',
            businessUnitId: 30,
        );
    }

    public function test_valid_until_must_follow_valid_from(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: 'supervisor',
            validFrom: new DateTimeImmutable(
                '2026-07-20 10:00:00'
            ),
            validUntil: new DateTimeImmutable(
                '2026-07-20 09:00:00'
            ),
        );
    }

    public function test_service_assigns_and_invalidates_cache(): void
    {
        $repository =
            new FakeContextRoleAssignmentRepository();

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new ContextRoleAssignmentService(
            assignments: $repository,
            cache: $cache,
        );

        $assignment = new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: 'supervisor',
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );

        $service->assign($assignment);

        $this->assertSame(
            $assignment,
            $repository->assigned
        );

        $this->assertSame(
            [10],
            $cache->invalidatedIdentityIds
        );
    }

    public function test_successful_revoke_invalidates_cache(): void
    {
        $repository =
            new FakeContextRoleAssignmentRepository(
                revokeResult: true
            );

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new ContextRoleAssignmentService(
            assignments: $repository,
            cache: $cache,
        );

        $assignment = new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: 'supervisor',
        );

        $this->assertTrue(
            $service->revoke($assignment)
        );

        $this->assertSame(
            [10],
            $cache->invalidatedIdentityIds
        );
    }

    public function test_failed_revoke_does_not_invalidate_cache(): void
    {
        $repository =
            new FakeContextRoleAssignmentRepository(
                revokeResult: false
            );

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new ContextRoleAssignmentService(
            assignments: $repository,
            cache: $cache,
        );

        $assignment = new ContextRoleAssignment(
            authIdentityId: 10,
            roleName: 'supervisor',
        );

        $this->assertFalse(
            $service->revoke($assignment)
        );

        $this->assertSame(
            [],
            $cache->invalidatedIdentityIds
        );
    }
}
