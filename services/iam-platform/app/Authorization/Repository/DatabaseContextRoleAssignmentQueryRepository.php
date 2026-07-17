<?php

namespace App\Authorization\Repository;

use App\Authorization\Assignment\Query\ContextRoleAssignmentFilter;
use App\Authorization\Assignment\Query\ContextRoleAssignmentView;
use App\Authorization\Contracts\ContextRoleAssignmentQueryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\ConnectionInterface;

class DatabaseContextRoleAssignmentQueryRepository implements
    ContextRoleAssignmentQueryRepository
{
    public function __construct(
        private readonly ConnectionInterface $database
    ) {
    }

    public function paginate(
        ContextRoleAssignmentFilter $filter
    ): LengthAwarePaginator {
        $query = $this->database
            ->table('access_role_contexts AS contexts')
            ->join(
                'roles AS roles',
                'roles.id',
                '=',
                'contexts.role_id'
            )
            ->leftJoin(
                'access_companies AS companies',
                'companies.id',
                '=',
                'contexts.company_id'
            )
            ->leftJoin(
                'access_business_units AS business_units',
                'business_units.id',
                '=',
                'contexts.business_unit_id'
            )
            ->leftJoin(
                'access_systems AS systems',
                'systems.id',
                '=',
                'contexts.system_id'
            )
            ->where('roles.guard_name', 'web')
            ->select([
                'contexts.id',
                'contexts.auth_identity_id',
                'contexts.role_id',
                'roles.name AS role_name',
                'contexts.company_id',
                'companies.name AS company_name',
                'contexts.business_unit_id',
                'business_units.name AS business_unit_name',
                'contexts.system_id',
                'systems.name AS system_name',
                'contexts.status',
                'contexts.valid_from',
                'contexts.valid_until',
                'contexts.created_at',
                'contexts.updated_at',
            ]);

        if ($filter->authIdentityId !== null) {
            $query->where(
                'contexts.auth_identity_id',
                $filter->authIdentityId
            );
        }

        if ($filter->companyId !== null) {
            $query->where(
                'contexts.company_scope_id',
                $filter->companyId
            );
        }

        if ($filter->businessUnitId !== null) {
            $query->where(
                'contexts.business_unit_scope_id',
                $filter->businessUnitId
            );
        }

        if ($filter->systemId !== null) {
            $query->where(
                'contexts.system_scope_id',
                $filter->systemId
            );
        }

        $roleName = $filter->normalizedRoleName();

        if ($roleName !== null) {
            $query->where(
                'roles.name',
                $roleName
            );
        }

        $status = $filter->normalizedStatus();

        if ($status !== null) {
            $query->where(
                'contexts.status',
                $status
            );
        }

        $paginator = $query
            ->orderByDesc('contexts.id')
            ->paginate(
                perPage: $filter->perPage,
                columns: ['*'],
                pageName: 'page',
                page: $filter->page,
            );

        $paginator->setCollection(
            $paginator
                ->getCollection()
                ->map(
                    static fn (object $row):
                        ContextRoleAssignmentView =>
                            ContextRoleAssignmentView::fromRow(
                                $row
                            )
                )
        );

        return $paginator;
    }
}
