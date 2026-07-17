<?php

namespace App\Authorization\Contracts;

interface AuthorizationCacheInvalidator
{
    public function invalidateIdentity(
        int $authIdentityId
    ): void;
}
