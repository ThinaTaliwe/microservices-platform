<?php

namespace App\Authorization\Contracts;

use App\Authorization\Membership\IdentityMembership;

interface IdentityMembershipRepository
{
    public function grant(
        IdentityMembership $membership
    ): void;

    public function revoke(
        IdentityMembership $membership
    ): bool;
}
