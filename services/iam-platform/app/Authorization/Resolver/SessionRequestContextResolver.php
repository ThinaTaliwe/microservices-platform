<?php

namespace App\Authorization\Resolver;

use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Context\TrustedRequestContext;
use Illuminate\Http\Request;

class SessionRequestContextResolver implements
    RequestContextResolver
{
    public function resolve(
        Request $request,
        ?int $componentId = null
    ): ContextResolution {
        $session = $request->session();

        $authIdentityId = $this->positiveInteger(
            $session->get('auth_identity_id')
        );

        if ($authIdentityId === null) {
            return ContextResolution::failure(
                'missing_auth_identity'
            );
        }

        $companyId = $this->positiveInteger(
            $session->get('active_company_id')
        );

        if ($companyId === null) {
            return ContextResolution::failure(
                'missing_active_company'
            );
        }

        $businessUnitId = $this->positiveInteger(
            $session->get('active_bu_id')
        );

        if ($businessUnitId === null) {
            return ContextResolution::failure(
                'missing_active_business_unit'
            );
        }

        $systemId = $this->positiveInteger(
            $session->get('active_system_id')
        );

        if ($systemId === null) {
            return ContextResolution::failure(
                'missing_active_system'
            );
        }

        if ($componentId !== null && $componentId < 1) {
            return ContextResolution::failure(
                'invalid_component'
            );
        }

        $trusted = new TrustedRequestContext(
            authIdentityId: $authIdentityId,
            companyId: $companyId,
            businessUnitId: $businessUnitId,
            systemId: $systemId,
            componentId: $componentId,
        );

        return ContextResolution::success(
            $trusted->toAccessContext()
        );
    }

    private function positiveInteger(
        mixed $value
    ): ?int {
        if (
            !is_int($value)
            && !(is_string($value) && ctype_digit($value))
        ) {
            return null;
        }

        $integer = (int) $value;

        return $integer > 0
            ? $integer
            : null;
    }
}
