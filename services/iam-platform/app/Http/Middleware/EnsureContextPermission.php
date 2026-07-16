<?php

namespace App\Http\Middleware;

use App\Authorization\Contracts\ComponentReferenceResolver;
use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Decision\AccessDecisionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureContextPermission
{
    public function __construct(
        private readonly RequestContextResolver $resolver,
        private readonly AccessDecisionService $decisions,
        private readonly ComponentReferenceResolver $components,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        ?string $componentReference = null,
    ): Response {
        $componentId = $this->resolveComponent(
            $componentReference
        );

        if (
            $componentReference !== null
            && trim($componentReference) !== ''
            && $componentId === null
        ) {
            $request->attributes->set(
                'iam_authorization_reason',
                'unknown_component'
            );

            throw new AccessDeniedHttpException(
                'Access denied.'
            );
        }

        $resolution = $this->resolver->resolve(
            $request,
            $componentId
        );

        if (
            !$resolution->resolved
            || $resolution->context === null
        ) {
            $request->attributes->set(
                'iam_authorization_reason',
                $resolution->reason
            );

            throw new AccessDeniedHttpException(
                'Access denied.'
            );
        }

        $decision = $this->decisions->decide(
            $resolution->context,
            $permission
        );

        $request->attributes->set(
            'iam_authorization_context',
            $resolution->context
        );

        $request->attributes->set(
            'iam_authorization_reason',
            $decision->reason
        );

        if (!$decision->allowed) {
            throw new AccessDeniedHttpException(
                'Access denied.'
            );
        }

        return $next($request);
    }

    private function resolveComponent(
        ?string $reference
    ): ?int {
        if ($reference === null || trim($reference) === '') {
            return null;
        }

        return $this->components->resolve(
            $reference
        );
    }
}
