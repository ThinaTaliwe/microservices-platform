<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Assignment\Query\ContextRoleAssignmentFilter;
use App\Authorization\Contracts\ContextRoleAssignmentQueryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FakeContextRoleAssignmentQueryRepository implements
    ContextRoleAssignmentQueryRepository
{
    public ?ContextRoleAssignmentFilter $receivedFilter =
        null;

    public function __construct(
        private readonly LengthAwarePaginator $result
    ) {
    }

    public function paginate(
        ContextRoleAssignmentFilter $filter
    ): LengthAwarePaginator {
        $this->receivedFilter = $filter;

        return $this->result;
    }
}
