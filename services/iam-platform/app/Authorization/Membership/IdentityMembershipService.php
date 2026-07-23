<?php

namespace App\Authorization\Membership;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;
use App\Authorization\Contracts\IdentityMembershipRepository;

class IdentityMembershipService
{
    public function __construct(
        private readonly IdentityMembershipRepository $memberships,
        private readonly AuthorizationCacheInvalidator $cache,
    ) {
    }

    public function grant(
        IdentityMembership $membership
    ): void {
        $this->memberships->grant($membership);

        $this->cache->invalidateIdentity(
            $membership->authIdentityId
        );
    }

    public function revoke(
        IdentityMembership $membership
    ): bool {
        $revoked = $this->memberships->revoke(
            $membership
        );

        if ($revoked) {
            $this->cache->invalidateIdentity(
                $membership->authIdentityId
            );
        }

        return $revoked;
    }
}
