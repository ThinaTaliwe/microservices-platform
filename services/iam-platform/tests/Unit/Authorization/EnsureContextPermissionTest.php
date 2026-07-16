<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Context\AccessContext;
use App\Authorization\Decision\AccessDecisionService;
use App\Authorization\Resolver\ContextResolution;
use App\Authorization\Snapshot\AccessSnapshot;
use App\Http\Middleware\EnsureContextPermission;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Unit\Authorization\Fakes\FakeContextAccessRepository;
use Tests\Unit\Authorization\Fakes\FakeRequestContextResolver;

class EnsureContextPermissionTest extends TestCase
{
    private AccessContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new AccessContext(
            authIdentityId: 1,
            companyId: 2,
            businessUnitId: 3,
            systemId: 4,
            componentId: 5,
        );
    }

    public function test_allowed_request_continues(): void
    {
        $middleware = $this->middleware(
            ContextResolution::success($this->context),
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: ['iam.sessions.read'],
                overrides: [],
            )
        );

        $request = Request::create(
            '/protected',
            'GET'
        );

        $response = $middleware->handle(
            $request,
            static fn (): \Symfony\Component\HttpFoundation\Response =>
                new \Symfony\Component\HttpFoundation\Response(
                    'allowed',
                    200
                ),
            'iam.sessions.read',
            '5'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());

        $this->assertSame(
            'permission_granted',
            $request->attributes->get(
                'iam_authorization_reason'
            )
        );

        $this->assertSame(
            $this->context,
            $request->attributes->get(
                'iam_authorization_context'
            )
        );
    }

    public function test_denied_permission_returns_403(): void
    {
        $middleware = $this->middleware(
            ContextResolution::success($this->context),
            new AccessSnapshot(
                companyAccess: true,
                businessUnitAccess: true,
                systemAccess: true,
                roles: [],
                permissions: [],
                overrides: [],
            )
        );

        $request = Request::create(
            '/protected',
            'GET'
        );

        try {
            $middleware->handle(
                $request,
                static fn (): never =>
                    throw new \RuntimeException(
                        'Request should not continue.'
                    ),
                'iam.sessions.revoke',
                '5'
            );

            $this->fail(
                'Expected authorization failure.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                403,
                $exception->getStatusCode()
            );

            $this->assertSame(
                'permission_not_granted',
                $request->attributes->get(
                    'iam_authorization_reason'
                )
            );
        }
    }

    public function test_unresolved_context_returns_403(): void
    {
        $middleware = $this->middleware(
            ContextResolution::failure(
                'missing_active_company'
            ),
            AccessSnapshot::denied()
        );

        $request = Request::create(
            '/protected',
            'GET'
        );

        try {
            $middleware->handle(
                $request,
                static fn (): never =>
                    throw new \RuntimeException(
                        'Request should not continue.'
                    ),
                'iam.sessions.read'
            );

            $this->fail(
                'Expected context-resolution failure.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                403,
                $exception->getStatusCode()
            );

            $this->assertSame(
                'missing_active_company',
                $request->attributes->get(
                    'iam_authorization_reason'
                )
            );
        }
    }

    public function test_invalid_component_is_rejected(): void
    {
        $resolver = new FakeRequestContextResolver(
            ContextResolution::failure(
                'invalid_component'
            )
        );

        $middleware = new EnsureContextPermission(
            resolver: $resolver,
            decisions: $this->decisionService(
                AccessSnapshot::denied()
            ),
        );

        $request = Request::create(
            '/protected',
            'GET'
        );

        try {
            $middleware->handle(
                $request,
                static fn (): never =>
                    throw new \RuntimeException(
                        'Request should not continue.'
                    ),
                'iam.sessions.read',
                'invalid'
            );

            $this->fail(
                'Expected invalid-component failure.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                403,
                $exception->getStatusCode()
            );

            $this->assertSame(
                0,
                $resolver->receivedComponentId
            );
        }
    }

    private function middleware(
        ContextResolution $resolution,
        AccessSnapshot $snapshot
    ): EnsureContextPermission {
        return new EnsureContextPermission(
            resolver: new FakeRequestContextResolver(
                $resolution
            ),
            decisions: $this->decisionService(
                $snapshot
            ),
        );
    }

    private function decisionService(
        AccessSnapshot $snapshot
    ): AccessDecisionService {
        return new AccessDecisionService(
            new FakeContextAccessRepository(
                $snapshot
            )
        );
    }
}
