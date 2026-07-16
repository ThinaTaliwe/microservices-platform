<?php

namespace App\Authorization\Decision;

use App\Authorization\Catalog\RoleCatalog;
use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Context\AccessContext;
use App\Authorization\Context\AccessEffect;
use App\Authorization\Snapshot\AccessSnapshot;

class AccessDecisionService
{
    /**
     * Request-lifetime memoization.
     *
     * @var array<string, AccessSnapshot>
     */
    private array $snapshots = [];

    public function __construct(
        private readonly ContextAccessRepository $repository
    ) {
    }

    public function decide(
        AccessContext $context,
        string $permission
    ): AccessDecision {
        $permission = trim($permission);

        if ($permission === '') {
            return AccessDecision::deny(
                'invalid_permission'
            );
        }

        $snapshot = $this->snapshot($context);

        if (!$snapshot->membershipsAreValid()) {
            return AccessDecision::deny(
                'invalid_context_membership'
            );
        }

        $override = $snapshot->overrideFor(
            $permission
        );

        if ($override === AccessEffect::DENY) {
            return AccessDecision::deny(
                'explicit_context_deny'
            );
        }

        if ($override === AccessEffect::ALLOW) {
            return AccessDecision::allow(
                'explicit_context_allow'
            );
        }

        if (
            $snapshot->hasRole(
                RoleCatalog::PLATFORM_SUPER_ADMIN
            )
        ) {
            return AccessDecision::allow(
                'platform_super_admin'
            );
        }

        if (!$snapshot->hasPermission($permission)) {
            return AccessDecision::deny(
                'permission_not_granted'
            );
        }

        return AccessDecision::allow();
    }

    public function can(
        AccessContext $context,
        string $permission
    ): bool {
        return $this->decide(
            $context,
            $permission
        )->allowed;
    }

    public function forget(
        ?AccessContext $context = null
    ): void {
        if ($context === null) {
            $this->snapshots = [];

            return;
        }

        unset(
            $this->snapshots[$context->cacheKey()]
        );
    }

    private function snapshot(
        AccessContext $context
    ): AccessSnapshot {
        $key = $context->cacheKey();

        return $this->snapshots[$key]
            ??= $this->repository->snapshot($context);
    }
}
