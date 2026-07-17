<?php

namespace App\Authorization\Contracts;

use App\Authorization\Assignment\Query\ContextRoleAssignmentFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContextRoleAssignmentQueryRepository
{
    /**
     * @return LengthAwarePaginator<
     *     int,
     *     \App\Authorization\Assignment\Query\ContextRoleAssignmentView
     * >
     */
    public function paginate(
        ContextRoleAssignmentFilter $filter
    ): LengthAwarePaginator;
}
