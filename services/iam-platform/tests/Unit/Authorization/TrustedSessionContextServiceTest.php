<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Context\TrustedSessionContext;
use App\Authorization\Resolver\TrustedSessionContextService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TrustedSessionContextServiceTest extends TestCase
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
        $this->seedValidContext();
    }

    public function test_it_resolves_approved_context(): void
    {
        $context = $this->service()->resolve(
            authIdentityId: 1,
            loginAttemptId: 10,
        );

        $this->assertSame(1, $context->authIdentityId);
        $this->assertSame(2, $context->companyId);
        $this->assertSame(3, $context->businessUnitId);
        $this->assertSame(4, $context->systemId);
    }

    public function test_it_uses_latest_approved_context_for_a_new_login_attempt(): void
    {
        $context = $this->service()->resolve(
            authIdentityId: 1,
            loginAttemptId: 99,
        );

        $this->assertSame(1, $context->authIdentityId);
        $this->assertSame(2, $context->companyId);
        $this->assertSame(3, $context->businessUnitId);
        $this->assertSame(4, $context->systemId);
    }

    public function test_it_stores_context_in_session(): void
    {
        $request = $this->requestWithSession();

        $this->service()->store(
            $request,
            new TrustedSessionContext(
                authIdentityId: 1,
                companyId: 2,
                businessUnitId: 3,
                systemId: 4,
            )
        );

        $this->assertSame(
            1,
            $request->session()->get(
                'auth_identity_id'
            )
        );

        $this->assertSame(
            2,
            $request->session()->get(
                'active_company_id'
            )
        );

        $this->assertSame(
            3,
            $request->session()->get(
                'active_bu_id'
            )
        );

        $this->assertSame(
            4,
            $request->session()->get(
                'active_system_id'
            )
        );
    }

    public function test_it_rejects_invalid_identity_id(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->service()->resolve(
            authIdentityId: 0,
            loginAttemptId: 10,
        );
    }

    public function test_it_rejects_missing_approval(): void
    {
        $this->database
            ->table('auth_pending_approvals')
            ->delete();

        $this->expectException(
            RuntimeException::class
        );

        $this->expectExceptionMessage(
            'An approved login context was not found.'
        );

        $this->service()->resolve(1, 10);
    }

    public function test_it_rejects_missing_bu_mapping(): void
    {
        $this->database
            ->table('access_business_units')
            ->delete();

        $this->expectException(
            RuntimeException::class
        );

        $this->expectExceptionMessage(
            'The approved business unit is not mapped'
        );

        $this->service()->resolve(1, 10);
    }

    public function test_it_rejects_missing_iam_system(): void
    {
        $this->database
            ->table('access_systems')
            ->delete();

        $this->expectException(
            RuntimeException::class
        );

        $this->expectExceptionMessage(
            'The IAM Platform system is unavailable.'
        );

        $this->service()->resolve(1, 10);
    }

    public function test_it_rejects_missing_role_assignment(): void
    {
        $this->database
            ->table('access_role_contexts')
            ->delete();

        $this->expectException(
            RuntimeException::class
        );

        $this->expectExceptionMessage(
            'The IAM identity has no active role assignment'
        );

        $this->service()->resolve(1, 10);
    }

    private function service(): TrustedSessionContextService
    {
        return new TrustedSessionContextService(
            $this->database
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
            'auth_pending_approvals',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->unsignedBigInteger(
                    'auth_identity_id'
                );
                $table->unsignedBigInteger(
                    'login_attempt_id'
                );
                $table->unsignedBigInteger(
                    'approved_bu_id'
                )->nullable();
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
                $table->string('external_key');
                $table->string('status');
            }
        );

        $schema->create(
            'access_systems',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->string('slug');
                $table->string('status');
            }
        );

        $schema->create(
            'access_role_contexts',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('id');
                $table->unsignedBigInteger(
                    'auth_identity_id'
                );
                $table->unsignedBigInteger(
                    'company_id'
                )->nullable();
                $table->unsignedBigInteger(
                    'business_unit_id'
                )->nullable();
                $table->unsignedBigInteger(
                    'system_id'
                )->nullable();
                $table->string('status');
                $table->timestamp(
                    'valid_from'
                )->nullable();
                $table->timestamp(
                    'valid_until'
                )->nullable();
            }
        );
    }

    private function seedValidContext(): void
    {
        $this->database
            ->table('auth_identities')
            ->insert([
                'id' => 1,
                'status' => 'active',
            ]);

        $this->database
            ->table('auth_pending_approvals')
            ->insert([
                'id' => 1,
                'auth_identity_id' => 1,
                'login_attempt_id' => 10,
                'approved_bu_id' => 200,
                'status' => 'approved',
            ]);

        $this->database
            ->table('access_companies')
            ->insert([
                'id' => 2,
                'status' => 'active',
            ]);

        $this->database
            ->table('access_business_units')
            ->insert([
                'id' => 3,
                'company_id' => 2,
                'external_key' => '1office-bu:200',
                'status' => 'active',
            ]);

        $this->database
            ->table('access_systems')
            ->insert([
                'id' => 4,
                'slug' => 'iam',
                'status' => 'active',
            ]);

        $this->database
            ->table('access_role_contexts')
            ->insert([
                'id' => 1,
                'auth_identity_id' => 1,
                'company_id' => null,
                'business_unit_id' => null,
                'system_id' => 4,
                'status' => 'active',
                'valid_from' => null,
                'valid_until' => null,
            ]);
    }

    private function requestWithSession(): Request
    {
        $request = Request::create(
            '/trusted-session-context-test',
            'GET'
        );

        $session = new Store(
            'trusted-session-context-test',
            new ArraySessionHandler(120)
        );

        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }
}
