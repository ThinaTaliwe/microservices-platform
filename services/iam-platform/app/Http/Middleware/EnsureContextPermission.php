<?php

namespace App\Http\Middleware;

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
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        ?string $componentId = null,
    ): Response {
        $resolution = $this->resolver->resolve(
            $request,
            $this->componentId($componentId)
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

    private function componentId(
        ?string $componentId
    ): ?int {
        if ($componentId === null || $componentId === '') {
            return null;
        }

        if (!ctype_digit($componentId)) {
            return 0;
        }

        return (int) $componentId;
    }
}
