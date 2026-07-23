<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Membership\IdentityMembership;
use App\Authorization\Membership\IdentityMembershipService;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeAuthorizationCacheInvalidator;
use Tests\Unit\Authorization\Fakes\FakeIdentityMembershipRepository;

class IdentityMembershipTest extends TestCase
{
    public function test_membership_exposes_context(): void
    {
        $membership = new IdentityMembership(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );

        $this->assertSame(10, $membership->authIdentityId);
        $this->assertSame(20, $membership->companyId);
        $this->assertSame(30, $membership->businessUnitId);
        $this->assertSame(40, $membership->systemId);
    }

    public function test_membership_rejects_invalid_identity(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new IdentityMembership(
            authIdentityId: 0,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );
    }

    public function test_valid_until_must_follow_valid_from(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new IdentityMembership(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
            validFrom: new DateTimeImmutable(
                '2026-07-23 10:00:00'
            ),
            validUntil: new DateTimeImmutable(
                '2026-07-23 09:00:00'
            ),
        );
    }

    public function test_service_grants_and_invalidates_cache(): void
    {
        $repository =
            new FakeIdentityMembershipRepository();

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new IdentityMembershipService(
            memberships: $repository,
            cache: $cache,
        );

        $membership = $this->membership();

        $service->grant($membership);

        $this->assertSame(
            $membership,
            $repository->granted
        );

        $this->assertSame(
            [10],
            $cache->invalidatedIdentityIds
        );
    }

    public function test_successful_revoke_invalidates_cache(): void
    {
        $repository =
            new FakeIdentityMembershipRepository(
                revokeResult: true
            );

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new IdentityMembershipService(
            memberships: $repository,
            cache: $cache,
        );

        $this->assertTrue(
            $service->revoke($this->membership())
        );

        $this->assertSame(
            [10],
            $cache->invalidatedIdentityIds
        );
    }

    public function test_failed_revoke_does_not_invalidate_cache(): void
    {
        $repository =
            new FakeIdentityMembershipRepository(
                revokeResult: false
            );

        $cache =
            new FakeAuthorizationCacheInvalidator();

        $service = new IdentityMembershipService(
            memberships: $repository,
            cache: $cache,
        );

        $this->assertFalse(
            $service->revoke($this->membership())
        );

        $this->assertSame(
            [],
            $cache->invalidatedIdentityIds
        );
    }

    private function membership(): IdentityMembership
    {
        return new IdentityMembership(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );
    }
}
