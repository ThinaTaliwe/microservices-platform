<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\Http\ContextRoleAssignmentFilterFactory;
use Illuminate\Http\Request;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContextRoleAssignmentFilterFactoryTest extends TestCase
{
    public function test_it_maps_request_query_parameters(): void
    {
        $request = Request::create(
            '/assignments',
            'GET',
            [
                'auth_identity_id' => '10',
                'company_id' => '20',
                'business_unit_id' => '30',
                'system_id' => '40',
                'role' => ' Supervisor ',
                'status' => ' ACTIVE ',
                'per_page' => '50',
                'page' => '2',
            ]
        );

        $filter = (
            new ContextRoleAssignmentFilterFactory()
        )->fromRequest($request);

        $this->assertSame(10, $filter->authIdentityId);
        $this->assertSame(20, $filter->companyId);
        $this->assertSame(30, $filter->businessUnitId);
        $this->assertSame(40, $filter->systemId);
        $this->assertSame(
            'supervisor',
            $filter->normalizedRoleName()
        );
        $this->assertSame(
            'active',
            $filter->normalizedStatus()
        );
        $this->assertSame(50, $filter->perPage);
        $this->assertSame(2, $filter->page);
    }

    public function test_it_uses_safe_pagination_defaults(): void
    {
        $request = Request::create(
            '/assignments',
            'GET'
        );

        $filter = (
            new ContextRoleAssignmentFilterFactory()
        )->fromRequest($request);

        $this->assertSame(25, $filter->perPage);
        $this->assertSame(1, $filter->page);
    }

    public function test_invalid_integer_is_rejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $request = Request::create(
            '/assignments',
            'GET',
            [
                'auth_identity_id' => 'invalid',
            ]
        );

        (
            new ContextRoleAssignmentFilterFactory()
        )->fromRequest($request);
    }
}
