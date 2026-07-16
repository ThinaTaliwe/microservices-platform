<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Catalog\RoleCatalog;
use App\Authorization\Context\AccessContext;
use App\Authorization\Context\AccessEffect;
use App\Authorization\Decision\AccessDecisionService;
use App\Authorization\Snapshot\AccessSnapshot;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeContextAccessRepository;

class AccessDecisionServiceTest extends TestCase
{
    private AccessContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
            componentId: 5,
        );
    }

    public function test_it_denies_invalid_membership(): void
    {
        $service = $this->service(
            AccessSnapshot::denied()
        );

        $decision = $service->decide(
            $this->context,
            'iam.sessions.read'
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame(
            'invalid_context_membership',
            $decision->reason
        );
    }

    public function test_explicit_deny_wins(): void
    {
        $service = $this->service(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [
                    RoleCatalog::PLATFORM_SUPER_ADMIN,
                ],
                permissions: [
                    'iam.sessions.read',
                ],
                overrides: [
                    'iam.sessions.read' =>
                        AccessEffect::DENY,
                ],
            )
        );

        $decision = $service->decide(
            $this->context,
            'iam.sessions.read'
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame(
            'explicit_context_deny',
            $decision->reason
        );
    }

    public function test_explicit_allow_grants_access(): void
    {
        $service = $this->service(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: [],
                overrides: [
                    'iam.sessions.read' =>
                        AccessEffect::ALLOW,
                ],
            )
        );

        $this->assertTrue(
            $service->can(
                $this->context,
                'iam.sessions.read'
            )
        );
    }

    public function test_super_admin_is_allowed(): void
    {
        $service = $this->service(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [
                    RoleCatalog::PLATFORM_SUPER_ADMIN,
                ],
                permissions: [],
                overrides: [],
            )
        );

        $this->assertTrue(
            $service->can(
                $this->context,
                'iam.permissions.manage'
            )
        );
    }

    public function test_normal_permission_is_allowed(): void
    {
        $service = $this->service(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: [
                    'iam.sessions.read',
                ],
                overrides: [],
            )
        );

        $this->assertTrue(
            $service->can(
                $this->context,
                'iam.sessions.read'
            )
        );
    }

    public function test_missing_permission_is_denied(): void
    {
        $service = $this->service(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: [],
                overrides: [],
            )
        );

        $decision = $service->decide(
            $this->context,
            'iam.sessions.revoke'
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame(
            'permission_not_granted',
            $decision->reason
        );
    }

    public function test_snapshot_is_loaded_once_per_context(): void
    {
        $repository = new FakeContextAccessRepository(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: [
                    'iam.sessions.read',
                ],
                overrides: [],
            )
        );

        $service = new AccessDecisionService(
            $repository
        );

        $service->can(
            $this->context,
            'iam.sessions.read'
        );

        $service->can(
            $this->context,
            'iam.sessions.read'
        );

        $this->assertSame(1, $repository->calls);
    }

    private function service(
        AccessSnapshot $snapshot
    ): AccessDecisionService {
        return new AccessDecisionService(
            new FakeContextAccessRepository(
                $snapshot
            )
        );
    }
}
