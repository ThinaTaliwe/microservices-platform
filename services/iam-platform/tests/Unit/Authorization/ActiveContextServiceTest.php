<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Context\ActiveContextService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\Authorization\Fakes\FakeAuthorizationCacheInvalidator;

class ActiveContextServiceTest extends TestCase
{
    private ConnectionInterface $database;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->seedContext();
    }

    public function test_it_lists_available_contexts(): void
    {
        $contexts = $this->service()->available(1);

        $this->assertCount(1, $contexts);
        $this->assertSame(10, $contexts[0]['company_id']);
        $this->assertSame(
            20,
            $contexts[0]['business_unit_id']
        );
        $this->assertSame(30, $contexts[0]['system_id']);
    }

    public function test_it_switches_to_an_authorized_context(): void
    {
        $request = $this->requestWithSession();

        $this->service()->switch(
            $request,
            companyId: 10,
            businessUnitId: 20,
            systemId: 30,
        );

        $this->assertSame(
            10,
            $request->session()->get(
                'active_company_id'
            )
        );

        $this->assertSame(
            20,
            $request->session()->get(
                'active_bu_id'
            )
        );

        $this->assertSame(
            30,
            $request->session()->get(
                'active_system_id'
            )
        );
    }

    public function test_it_rejects_an_unavailable_context(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service()->switch(
            $this->requestWithSession(),
            companyId: 999,
            businessUnitId: 999,
            systemId: 999,
        );
    }

    private function service(): ActiveContextService
    {
        return new ActiveContextService(
            database: $this->database,
            cache:
                new FakeAuthorizationCacheInvalidator(),
        );
    }

    private function requestWithSession(): Request
    {
        $request = Request::create(
            '/context-switch',
            'POST'
        );

        $session = new Store(
            'active-context-test',
            new ArraySessionHandler(120)
        );

        $session->start();
        $session->put('auth_identity_id', 1);

        $request->setLaravelSession($session);

        return $request;
    }

    private function createSchema(): void
    {
        $schema = $this->database
            ->getSchemaBuilder();

        $schema->create(
            'access_companies',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('name');
                $table->string('status');
            }
        );

        $schema->create(
            'access_business_units',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->string('status');
            }
        );

        $schema->create(
            'access_systems',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('name');
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
                }
            );
        }
    }

    private function seedContext(): void
    {
        $this->database
            ->table('access_companies')
            ->insert([
                'id' => 10,
                'name' => 'Company',
                'status' => 'active',
            ]);

        $this->database
            ->table('access_business_units')
            ->insert([
                'id' => 20,
                'company_id' => 10,
                'name' => 'Business Unit',
                'status' => 'active',
            ]);

        $this->database
            ->table('access_systems')
            ->insert([
                'id' => 30,
                'name' => 'IAM Platform',
                'status' => 'active',
            ]);

        $this->database
            ->table('access_identity_companies')
            ->insert([
                'auth_identity_id' => 1,
                'company_id' => 10,
                'status' => 'active',
            ]);

        $this->database
            ->table(
                'access_identity_business_units'
            )
            ->insert([
                'auth_identity_id' => 1,
                'business_unit_id' => 20,
                'status' => 'active',
            ]);

        $this->database
            ->table('access_identity_systems')
            ->insert([
                'auth_identity_id' => 1,
                'system_id' => 30,
                'status' => 'active',
            ]);
    }
}
