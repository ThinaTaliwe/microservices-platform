<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\Query\ContextRoleAssignmentFilter;
use App\Authorization\Assignment\Query\ContextRoleAssignmentQueryService;
use App\Authorization\Assignment\Query\ContextRoleAssignmentView;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Authorization\Fakes\FakeContextRoleAssignmentQueryRepository;

class ContextRoleAssignmentQueryTest extends TestCase
{
    public function test_filter_normalizes_values(): void
    {
        $filter = new ContextRoleAssignmentFilter(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
            roleName: ' Supervisor ',
            status: ' ACTIVE ',
            perPage: 50,
            page: 2,
        );

        $this->assertSame(
            'supervisor',
            $filter->normalizedRoleName()
        );

        $this->assertSame(
            'active',
            $filter->normalizedStatus()
        );
    }

    public function test_business_unit_filter_requires_company(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new ContextRoleAssignmentFilter(
            businessUnitId: 30
        );
    }

    public function test_page_size_is_bounded(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new ContextRoleAssignmentFilter(
            perPage: 101
        );
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        new ContextRoleAssignmentFilter(
            status: 'deleted'
        );
    }

    public function test_view_maps_database_row(): void
    {
        $view = ContextRoleAssignmentView::fromRow(
            (object) [
                'id' => 1,
                'auth_identity_id' => 10,
                'role_id' => 11,
                'role_name' => 'supervisor',
                'company_id' => 20,
                'company_name' => 'Company',
                'business_unit_id' => 30,
                'business_unit_name' => 'BU',
                'system_id' => 40,
                'system_name' => 'IAM Platform',
                'status' => 'active',
                'valid_from' => null,
                'valid_until' => null,
                'created_at' => '2026-07-17 08:00:00',
                'updated_at' => '2026-07-17 08:00:00',
            ]
        );

        $this->assertSame(
            'supervisor',
            $view->roleName
        );

        $this->assertSame(
            30,
            $view->businessUnitId
        );
    }

    public function test_service_delegates_to_repository(): void
    {
        $paginator = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 25,
            currentPage: 1,
        );

        $repository =
            new FakeContextRoleAssignmentQueryRepository(
                $paginator
            );

        $service =
            new ContextRoleAssignmentQueryService(
                $repository
            );

        $filter = new ContextRoleAssignmentFilter(
            authIdentityId: 10
        );

        $result = $service->paginate($filter);

        $this->assertSame(
            $paginator,
            $result
        );

        $this->assertSame(
            $filter,
            $repository->receivedFilter
        );
    }
}
