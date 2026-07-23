<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\IdentityMembershipRepository;
use App\Authorization\Membership\IdentityMembership;

class FakeIdentityMembershipRepository implements
    IdentityMembershipRepository
{
    public ?IdentityMembership $granted = null;

    public ?IdentityMembership $revoked = null;

    public function __construct(
        private readonly bool $revokeResult = true
    ) {
    }

    public function grant(
        IdentityMembership $membership
    ): void {
        $this->granted = $membership;
    }

    public function revoke(
        IdentityMembership $membership
    ): bool {
        $this->revoked = $membership;

        return $this->revokeResult;
    }
}
