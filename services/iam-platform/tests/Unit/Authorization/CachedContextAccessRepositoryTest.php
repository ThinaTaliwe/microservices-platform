<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Context\AccessContext;
use App\Authorization\Repository\CachedContextAccessRepository;
use App\Authorization\Snapshot\AccessSnapshot;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeContextAccessRepository;

class CachedContextAccessRepositoryTest extends TestCase
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

    public function test_repeated_context_uses_shared_cache(): void
    {
        [$cached, $inner] = $this->repository();

        $cached->snapshot($this->context);
        $cached->snapshot($this->context);

        $this->assertSame(1, $inner->calls);
    }

    public function test_different_context_uses_separate_cache_entry(): void
    {
        [$cached, $inner] = $this->repository();

        $cached->snapshot($this->context);

        $cached->snapshot(
            new AccessContext(
                authIdentityId: 1,
                companyId: 2,
                businessUnitId: 3,
                systemId: 4,
                componentId: 6,
            )
        );

        $this->assertSame(2, $inner->calls);
    }

    public function test_identity_invalidation_reloads_snapshot(): void
    {
        [$cached, $inner] = $this->repository();

        $cached->snapshot($this->context);

        $cached->invalidateIdentity(1);

        $cached->snapshot($this->context);

        $this->assertSame(2, $inner->calls);
    }

    public function test_disabled_cache_always_uses_repository(): void
    {
        [$cached, $inner] = $this->repository(
            enabled: false
        );

        $cached->snapshot($this->context);
        $cached->snapshot($this->context);

        $this->assertSame(2, $inner->calls);
    }

    /**
     * @return array{
     *     CachedContextAccessRepository,
     *     FakeContextAccessRepository
     * }
     */
    private function repository(
        bool $enabled = true
    ): array {
        $inner = new FakeContextAccessRepository(
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: ['supervisor'],
                permissions: ['iam.sessions.read'],
                overrides: [],
            )
        );

        $cached = new CachedContextAccessRepository(
            repository: $inner,
            cache: new CacheRepository(
                new ArrayStore()
            ),
            enabled: $enabled,
            ttlSeconds: 900,
            prefix: 'test:iam:authorization',
        );

        return [$cached, $inner];
    }
}
