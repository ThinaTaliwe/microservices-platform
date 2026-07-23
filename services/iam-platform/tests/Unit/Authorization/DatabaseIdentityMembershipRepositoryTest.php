<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Membership\IdentityMembership;
use App\Authorization\Repository\DatabaseIdentityMembershipRepository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DatabaseIdentityMembershipRepositoryTest extends TestCase
{
    private Capsule $capsule;

    private ConnectionInterface $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capsule = new Capsule();

        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->database = $this->capsule
            ->getConnection();

        $this->createSchema();
        $this->seedCatalogue();
    }

    public function test_it_grants_all_three_memberships(): void
    {
        $this->repository()->grant(
            $this->membership()
        );

        $this->assertDatabaseMembership(
            'access_identity_companies',
            'company_id',
            20
        );

        $this->assertDatabaseMembership(
            'access_identity_business_units',
            'business_unit_id',
            30
        );

        $this->assertDatabaseMembership(
            'access_identity_systems',
            'system_id',
            40
        );
    }

    public function test_grant_is_idempotent(): void
    {
        $repository = $this->repository();

        $repository->grant($this->membership());
        $repository->grant($this->membership());

        $this->assertSame(
            1,
            $this->database
                ->table('access_identity_companies')
                ->count()
        );

        $this->assertSame(
            1,
            $this->database
                ->table(
                    'access_identity_business_units'
                )
                ->count()
        );

        $this->assertSame(
            1,
            $this->database
                ->table('access_identity_systems')
                ->count()
        );
    }

    public function test_it_rejects_a_bu_from_another_company(): void
    {
        $this->database
            ->table('access_business_units')
            ->where('id', 30)
            ->update([
                'company_id' => 999,
            ]);

        $this->expectException(
            RuntimeException::class
        );

        $this->repository()->grant(
            $this->membership()
        );
    }

    public function test_revoke_disables_bu_and_unused_company(): void
    {
        $repository = $this->repository();

        $repository->grant($this->membership());

        $this->assertTrue(
            $repository->revoke(
                $this->membership()
            )
        );

        $this->assertSame(
            'revoked',
            $this->database
                ->table(
                    'access_identity_business_units'
                )
                ->value('status')
        );

        $this->assertSame(
            'revoked',
            $this->database
                ->table('access_identity_companies')
                ->value('status')
        );

        $this->assertSame(
            'active',
            $this->database
                ->table('access_identity_systems')
                ->value('status')
        );
    }

    private function repository():
        DatabaseIdentityMembershipRepository
    {
        return new DatabaseIdentityMembershipRepository(
            $this->database
        );
    }

    private function membership(): IdentityMembership
    {
        return new IdentityMembership(
            authIdentityId: 10,
            companyId: 20,
            businessUnitId: 30,
            systemId: 40,
        );
    }

    private function assertDatabaseMembership(
        string $table,
        string $scopeColumn,
        int $scopeId
    ): void {
        $this->assertTrue(
            $this->database
                ->table($table)
                ->where('auth_identity_id', 10)
                ->where($scopeColumn, $scopeId)
                ->where('status', 'active')
                ->exists()
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
                $table->unsignedBigInteger('company_id');
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

        foreach ([
            [
                'access_identity_companies',
                'company_id',
            ],
            [
                'access_identity_business_units',
                'business_unit_id',
            ],
            [
                'access_identity_systems',
                'system_id',
            ],
        ] as [$tableName, $scopeColumn]) {
            $schema->create(
                $tableName,
                function (Blueprint $table) use (
                    $scopeColumn
                ): void {
                    $table->unsignedBigInteger(
                        'auth_identity_id'
                    );

                    $table->unsignedBigInteger(
                        $scopeColumn
                    );

                    $table->string('status');
                    $table->timestamp(
                        'valid_from'
                    )->nullable();
                    $table->timestamp(
                        'valid_until'
                    )->nullable();
                    $table->timestamp(
                        'created_at'
                    )->nullable();
                    $table->timestamp(
                        'updated_at'
                    )->nullable();

                    $table->primary([
                        'auth_identity_id',
                        $scopeColumn,
                    ]);
                }
            );
        }
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
                'id' => 20,
                'status' => 'active',
            ]);

        $this->database
            ->table('access_business_units')
            ->insert([
                'id' => 30,
                'company_id' => 20,
                'status' => 'active',
            ]);

        $this->database
            ->table('access_systems')
            ->insert([
                'id' => 40,
                'status' => 'active',
            ]);
    }
}
