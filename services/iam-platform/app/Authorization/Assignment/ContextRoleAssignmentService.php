<?php

namespace App\Authorization\Assignment;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;
use App\Authorization\Contracts\ContextRoleAssignmentRepository;

class ContextRoleAssignmentService
{
    public function __construct(
        private readonly ContextRoleAssignmentRepository $assignments,
        private readonly AuthorizationCacheInvalidator $cache,
    ) {
    }

    public function assign(
        ContextRoleAssignment $assignment
    ): void {
        $this->assignments->assign($assignment);

        $this->cache->invalidateIdentity(
            $assignment->authIdentityId
        );
    }

    public function revoke(
        ContextRoleAssignment $assignment
    ): bool {
        $revoked = $this->assignments->revoke(
            $assignment
        );

        if ($revoked) {
            $this->cache->invalidateIdentity(
                $assignment->authIdentityId
            );
        }

        return $revoked;
    }
}
