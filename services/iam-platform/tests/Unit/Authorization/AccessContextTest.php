<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Context\AccessContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AccessContextTest extends TestCase
{
    public function test_it_exposes_context_as_array(): void
    {
        $context = new AccessContext(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
            componentId: 50,
        );

        $this->assertSame([
            'auth_identity_id' => 10,
            'company_id' => 20,
            'business_unit_id' => 30,
            'system_id' => 40,
            'component_id' => 50,
        ], $context->toArray());
    }

    public function test_it_builds_a_stable_cache_key(): void
    {
        $context = new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
        );

        $this->assertSame(
            'iam-v2:access-scope:1:2:3:4:component:none',
            $context->cacheKey()
        );
    }

    public function test_scope_key_ignores_component(): void
    {
        $first = new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
            componentId: 5,
        );

        $second = new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
            componentId: 99,
        );

        $this->assertSame(
            $first->scopeCacheKey(),
            $second->scopeCacheKey()
        );

        $this->assertNotSame(
            $first->cacheKey(),
            $second->cacheKey()
        );
    }


    public function test_it_rejects_invalid_identity_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AccessContext(
            authIdentityId: 0,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
        );
    }

    public function test_it_rejects_invalid_component_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
            componentId: 0,
        );
    }
}
