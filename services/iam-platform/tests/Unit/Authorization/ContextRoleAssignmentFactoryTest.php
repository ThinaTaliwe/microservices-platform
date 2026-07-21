<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\Http\ContextRoleAssignmentFactory;
use Illuminate\Http\Request;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContextRoleAssignmentFactoryTest extends TestCase
{
    public function test_it_builds_assignment_from_request(): void
    {
        $request = Request::create(
            '/iam-v2/role-assignments',
            'POST',
            [
                'auth_identity_id' => '3',
                'role' => ' Supervisor ',
                'company_id' => '25',
                'business_unit_id' => '1',
                'system_id' => '1',
                'valid_from' => '2026-07-21T12:00',
                'valid_until' => '2026-07-22T12:00',
            ]
        );

        $assignment = (
            new ContextRoleAssignmentFactory()
        )->fromRequest($request);

        $this->assertSame(
            3,
            $assignment->authIdentityId
        );

        $this->assertSame(
            'supervisor',
            $assignment->normalizedRoleName()
        );

        $this->assertSame(
            25,
            $assignment->companyId
        );

        $this->assertSame(
            1,
            $assignment->businessUnitId
        );

        $this->assertSame(
            '2026-07-21 12:00:00',
            $assignment->validFrom?->format(
                'Y-m-d H:i:s'
            )
        );
    }

    public function test_missing_identity_is_rejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        (
            new ContextRoleAssignmentFactory()
        )->fromRequest(
            Request::create(
                '/iam-v2/role-assignments',
                'POST',
                [
                    'role' => 'supervisor',
                ]
            )
        );
    }

    public function test_invalid_date_is_rejected(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        (
            new ContextRoleAssignmentFactory()
        )->fromRequest(
            Request::create(
                '/iam-v2/role-assignments',
                'POST',
                [
                    'auth_identity_id' => '1',
                    'role' => 'supervisor',
                    'valid_from' => 'invalid-date',
                ]
            )
        );
    }

    public function test_business_unit_requires_company(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        (
            new ContextRoleAssignmentFactory()
        )->fromRequest(
            Request::create(
                '/iam-v2/role-assignments',
                'POST',
                [
                    'auth_identity_id' => '1',
                    'role' => 'bu-admin',
                    'business_unit_id' => '1',
                ]
            )
        );
    }
}
