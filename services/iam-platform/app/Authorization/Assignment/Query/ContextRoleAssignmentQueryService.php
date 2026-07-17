<?php

namespace App\Authorization\Assignment\Query;

use App\Authorization\Contracts\ContextRoleAssignmentQueryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContextRoleAssignmentQueryService
{
    public function __construct(
        private readonly ContextRoleAssignmentQueryRepository $repository
    ) {
    }

    /**
     * @return LengthAwarePaginator<
     *     int,
     *     ContextRoleAssignmentView
     * >
     */
    public function paginate(
        ContextRoleAssignmentFilter $filter
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            $filter
        );
    }
}
