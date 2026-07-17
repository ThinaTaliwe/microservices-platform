<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Assignment\ContextRoleAssignment;
use App\Authorization\Contracts\ContextRoleAssignmentRepository;

class FakeContextRoleAssignmentRepository implements
    ContextRoleAssignmentRepository
{
    public ?ContextRoleAssignment $assigned = null;
    public ?ContextRoleAssignment $revoked = null;

    public function __construct(
        public bool $revokeResult = true
    ) {
    }

    public function assign(
        ContextRoleAssignment $assignment
    ): void {
        $this->assigned = $assignment;
    }

    public function revoke(
        ContextRoleAssignment $assignment
    ): bool {
        $this->revoked = $assignment;

        return $this->revokeResult;
    }
}
