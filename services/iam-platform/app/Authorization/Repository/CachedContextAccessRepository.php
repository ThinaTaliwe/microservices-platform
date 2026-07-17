<?php

namespace App\Authorization\Repository;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;
use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Context\AccessContext;
use App\Authorization\Snapshot\AccessSnapshot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachedContextAccessRepository implements
    ContextAccessRepository,
    AuthorizationCacheInvalidator
{
    public function __construct(
        private readonly ContextAccessRepository $repository,
        private readonly CacheRepository $cache,
        private readonly bool $enabled = true,
        private readonly int $ttlSeconds = 900,
        private readonly string $prefix =
            'iam:v2:authorization',
    ) {
    }

    public function snapshot(
        AccessContext $context
    ): AccessSnapshot {
        if (!$this->enabled) {
            return $this->repository->snapshot(
                $context
            );
        }

        $payload = $this->cache->remember(
            $this->snapshotKey($context),
            max(1, $this->ttlSeconds),
            fn (): array => $this->repository
                ->snapshot($context)
                ->toArray()
        );

        return AccessSnapshot::fromArray(
            $payload
        );
    }

    /**
     * Invalidates all cached contexts for one identity
     * without scanning or deleting individual cache keys.
     */
    public function invalidateIdentity(
        int $authIdentityId
    ): void {
        $this->assertPositiveIdentityId(
            $authIdentityId
        );

        $key = $this->versionKey(
            $authIdentityId
        );

        /*
         * add() is atomic on Redis and creates version 2
         * when no prior version exists.
         */
        if ($this->cache->add($key, 2)) {
            return;
        }

        $this->cache->increment($key);
    }

    private function snapshotKey(
        AccessContext $context
    ): string {
        return implode(':', [
            $this->prefix,
            'snapshot',
            'v' . $this->identityVersion(
                $context->authIdentityId
            ),
            $context->cacheKey(),
        ]);
    }

    private function identityVersion(
        int $authIdentityId
    ): int {
        $key = $this->versionKey(
            $authIdentityId
        );

        $this->cache->add($key, 1);

        return max(
            1,
            (int) $this->cache->get($key, 1)
        );
    }

    private function versionKey(
        int $authIdentityId
    ): string {
        return implode(':', [
            $this->prefix,
            'identity',
            $authIdentityId,
            'version',
        ]);
    }

    private function assertPositiveIdentityId(
        int $authIdentityId
    ): void {
        if ($authIdentityId < 1) {
            throw new \InvalidArgumentException(
                'authIdentityId must be positive.'
            );
        }
    }
}
