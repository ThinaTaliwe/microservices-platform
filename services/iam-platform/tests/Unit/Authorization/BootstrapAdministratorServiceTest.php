<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Assignment\ContextRoleAssignment;
use App\Authorization\Assignment\ContextRoleAssignmentService;
use App\Authorization\Bootstrap\BootstrapAdministratorService;
use App\Authorization\Membership\IdentityMembership;
use App\Authorization\Membership\IdentityMembershipService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Tests\TestCase;
use RuntimeException;

class BootstrapAdministratorServiceTest extends TestCase
{
    private ConnectionInterface $database;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'bootstrap-admins.emails' => [
                'korrie@bchem.co.za',
                'thina.taliwe2@gmail.com',
            ],
        ]);

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->database = $capsule->getConnection();

        $this->createSchema();
        $this->seedCatalogue();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_recognizes_configured_emails():
        void
    {
        $service = $this->service();

        $this->assertTrue(
            $service->isBootstrapAdministrator(
                'KORRIE@BCHEM.CO.ZA'
            )
        );

        $this->assertTrue(
            $service->isBootstrapAdministrator(
                ' thina.taliwe2@gmail.com '
            )
        );

        $this->assertFalse(
            $service->isBootstrapAdministrator(
                'ordinary@example.com'
            )
        );
    }

    public function test_it_rejects_an_unconfigured_email():
        void
    {
        $this->expectException(
            RuntimeException::class
        );

        $this->expectExceptionMessage(
            'not a configured bootstrap administrator'
        );

        $this->service()->provision(
            authIdentityId: 10,
            email: 'ordinary@example.com',
        );
    }

    public function test_it_provisions_all_active_contexts_and_role():
        void
    {
        $membershipService = Mockery::mock(
            IdentityMembershipService::class
        );

        $roleService = Mockery::mock(
            ContextRoleAssignmentService::class
        );

        $grantedMemberships = [];

        $membershipService
            ->shouldReceive('grant')
            ->times(4)
            ->withArgs(
                function (
                    IdentityMembership $membership
                ) use (
                    &$grantedMemberships
                ): bool {
                    $grantedMemberships[] = [
                        $membership->companyId,
                        $membership->businessUnitId,
                        $membership->systemId,
                    ];

                    return true;
                }
            );

        $roleService
            ->shouldReceive('assign')
            ->once()
            ->withArgs(
                static function (
                    ContextRoleAssignment $assignment
                ): bool {
                    return
                        $assignment->authIdentityId === 10
                        && $assignment
                            ->normalizedRoleName()
                            === 'platform-super-admin'
                        && $assignment->companyId === null
                        && $assignment
                            ->businessUnitId === null
                        && $assignment->systemId === null;
                }
            );

        $service =
            new BootstrapAdministratorService(
                database: $this->database,
                memberships: $membershipService,
                roles: $roleService,
            );

        $result = $service->provision(
            authIdentityId: 10,
            email: 'korrie@bchem.co.za',
        );

        sort($grantedMemberships);

        $this->assertSame(
            [
                [20, 30, 50],
                [20, 30, 60],
                [40, 70, 50],
                [40, 70, 60],
            ],
            $grantedMemberships
        );

        $this->assertSame(2, $result['company_count']);
        $this->assertSame(
            2,
            $result['business_unit_count']
        );
        $this->assertSame(2, $result['system_count']);
        $this->assertSame(4, $result['context_count']);
        $this->assertSame(
            'platform-super-admin',
            $result['role']
        );
    }

    private function service():
        BootstrapAdministratorService
    {
        return new BootstrapAdministratorService(
            database: $this->database,
            memberships: Mockery::mock(
                IdentityMembershipService::class
            ),
            roles: Mockery::mock(
                ContextRoleAssignmentService::class
            ),
        );
    }

    private function createSchema(): void
    {
        $schema = $this->database
            ->getSchemaBuilder();

        $schema->create(
            'auth_identities',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('status');
            }
        );

        $schema->create(
            'access_companies',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('status');
            }
        );

        $schema->create(
            'access_business_units',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->unsignedBigInteger(
                    'company_id'
                );
                $table->string('status');
            }
        );

        $schema->create(
            'access_systems',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('status');
            }
        );
    }

    private function seedCatalogue(): void
    {
        $this->database
            ->table('auth_identities')
            ->insert([
                'id' => 10,
                'status' => 'active',
            ]);

        $this->database
            ->table('access_companies')
            ->insert([
                [
                    'id' => 20,
                    'status' => 'active',
                ],
                [
                    'id' => 40,
                    'status' => 'active',
                ],
                [
                    'id' => 90,
                    'status' => 'inactive',
                ],
            ]);

        $this->database
            ->table('access_business_units')
            ->insert([
                [
                    'id' => 30,
                    'company_id' => 20,
                    'status' => 'active',
                ],
                [
                    'id' => 70,
                    'company_id' => 40,
                    'status' => 'active',
                ],
                [
                    'id' => 80,
                    'company_id' => 90,
                    'status' => 'active',
                ],
            ]);

        $this->database
            ->table('access_systems')
            ->insert([
                [
                    'id' => 50,
                    'status' => 'active',
                ],
                [
                    'id' => 60,
                    'status' => 'active',
                ],
                [
                    'id' => 100,
                    'status' => 'inactive',
                ],
            ]);
    }
}
