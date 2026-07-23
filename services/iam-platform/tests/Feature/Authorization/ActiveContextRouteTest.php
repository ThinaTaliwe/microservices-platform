<?php

namespace Tests\Feature\Authorization;

use App\Authorization\Context\ActiveContextService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ActiveContextRouteTest extends TestCase
{
    public function test_missing_session_returns_401(): void
    {
        $this->getJson('/iam-v2/active-contexts')
            ->assertStatus(401);
    }

    public function test_it_returns_available_contexts(): void
    {
        $service = Mockery::mock(
            ActiveContextService::class
        );

        $service
            ->shouldReceive('available')
            ->once()
            ->with(10)
            ->andReturn([
                [
                    'company_id' => 20,
                    'company_name' => 'Company',
                    'business_unit_id' => 30,
                    'business_unit_name' =>
                        'Business Unit',
                    'system_id' => 40,
                    'system_name' => 'IAM Platform',
                ],
            ]);

        $this->app->instance(
            ActiveContextService::class,
            $service
        );

        $this
            ->withSession([
                'auth_identity_id' => 10,
                'active_company_id' => 20,
                'active_bu_id' => 30,
                'active_system_id' => 40,
            ])
            ->getJson('/iam-v2/active-contexts')
            ->assertOk()
            ->assertJsonPath(
                'data.0.business_unit_id',
                30
            )
            ->assertJsonPath(
                'active.company_id',
                20
            );
    }

    public function test_it_switches_context(): void
    {
        $service = Mockery::mock(
            ActiveContextService::class
        );

        $service
            ->shouldReceive('switch')
            ->once()
            ->withArgs(
                static function (
                    $request,
                    int $companyId,
                    int $businessUnitId,
                    int $systemId
                ): bool {
                    return $companyId === 20
                        && $businessUnitId === 30
                        && $systemId === 40;
                }
            );

        $this->app->instance(
            ActiveContextService::class,
            $service
        );

        $this
            ->withSession([
                'auth_identity_id' => 10,
            ])
            ->postJson(
                '/iam-v2/active-context',
                [
                    'company_id' => 20,
                    'business_unit_id' => 30,
                    'system_id' => 40,
                ]
            )
            ->assertOk()
            ->assertJsonPath(
                'active.business_unit_id',
                30
            );
    }

    public function test_unavailable_context_returns_422(): void
    {
        $service = Mockery::mock(
            ActiveContextService::class
        );

        $service
            ->shouldReceive('switch')
            ->once()
            ->andThrow(
                new RuntimeException(
                    'The selected IAM context is unavailable.'
                )
            );

        $this->app->instance(
            ActiveContextService::class,
            $service
        );

        $this
            ->withSession([
                'auth_identity_id' => 10,
            ])
            ->postJson(
                '/iam-v2/active-context',
                [
                    'company_id' => 999,
                    'business_unit_id' => 999,
                    'system_id' => 999,
                ]
            )
            ->assertStatus(422);
    }
}
