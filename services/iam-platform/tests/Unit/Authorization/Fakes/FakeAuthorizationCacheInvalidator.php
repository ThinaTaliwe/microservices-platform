<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;

class FakeAuthorizationCacheInvalidator implements
    AuthorizationCacheInvalidator
{
    /**
     * @var list<int>
     */
    public array $invalidatedIdentityIds = [];

    public function invalidateIdentity(
        int $authIdentityId
    ): void {
        $this->invalidatedIdentityIds[] =
            $authIdentityId;
    }
}
