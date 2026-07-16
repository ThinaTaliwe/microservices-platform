<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Resolver\SessionRequestContextResolver;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;

class SessionRequestContextResolverTest extends TestCase
{
    public function test_it_resolves_trusted_session_context(): void
    {
        $request = $this->requestWithSession([
            'auth_identity_id' => 10,
            'active_company_id' => 20,
            'active_bu_id' => 30,
            'active_system_id' => 40,
        ]);

        $resolution = (
            new SessionRequestContextResolver()
        )->resolve($request, 50);

        $this->assertTrue($resolution->resolved);
        $this->assertSame(
            [
                'auth_identity_id' => 10,
                'company_id' => 20,
                'business_unit_id' => 30,
                'system_id' => 40,
                'component_id' => 50,
            ],
            $resolution->context?->toArray()
        );
    }

    public function test_it_rejects_missing_identity(): void
    {
        $request = $this->requestWithSession([
            'active_company_id' => 20,
            'active_bu_id' => 30,
            'active_system_id' => 40,
        ]);

        $resolution = (
            new SessionRequestContextResolver()
        )->resolve($request);

        $this->assertFalse($resolution->resolved);
        $this->assertSame(
            'missing_auth_identity',
            $resolution->reason
        );
    }

    public function test_it_rejects_missing_company(): void
    {
        $request = $this->requestWithSession([
            'auth_identity_id' => 10,
            'active_bu_id' => 30,
            'active_system_id' => 40,
        ]);

        $resolution = (
            new SessionRequestContextResolver()
        )->resolve($request);

        $this->assertFalse($resolution->resolved);
        $this->assertSame(
            'missing_active_company',
            $resolution->reason
        );
    }

    public function test_it_rejects_invalid_component(): void
    {
        $request = $this->requestWithSession([
            'auth_identity_id' => 10,
            'active_company_id' => 20,
            'active_bu_id' => 30,
            'active_system_id' => 40,
        ]);

        $resolution = (
            new SessionRequestContextResolver()
        )->resolve($request, 0);

        $this->assertFalse($resolution->resolved);
        $this->assertSame(
            'invalid_component',
            $resolution->reason
        );
    }

    public function test_it_does_not_trust_request_input(): void
    {
        $request = $this->requestWithSession([
            'auth_identity_id' => 10,
            'active_company_id' => 20,
            'active_bu_id' => 30,
            'active_system_id' => 40,
        ]);

        $request->query->set(
            'active_company_id',
            999
        );

        $resolution = (
            new SessionRequestContextResolver()
        )->resolve($request);

        $this->assertSame(
            20,
            $resolution->context?->companyId
        );
    }

    /**
     * @param array<string, mixed> $values
     */
    private function requestWithSession(
        array $values
    ): Request {
        $request = Request::create(
            '/authorization-test',
            'GET'
        );

        $session = new Store(
            'authorization-test',
            new ArraySessionHandler(120)
        );

        $session->start();
        $session->put($values);

        $request->setLaravelSession($session);

        return $request;
    }
}
