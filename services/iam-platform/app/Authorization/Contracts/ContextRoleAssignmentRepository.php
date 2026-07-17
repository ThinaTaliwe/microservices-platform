<?php

namespace App\Authorization\Contracts;

use App\Authorization\Assignment\ContextRoleAssignment;

interface ContextRoleAssignmentRepository
{
    public function assign(
        ContextRoleAssignment $assignment
    ): void;

    public function revoke(
        ContextRoleAssignment $assignment
    ): bool;
}
