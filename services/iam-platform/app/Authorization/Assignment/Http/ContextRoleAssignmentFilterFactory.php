<?php

namespace App\Authorization\Assignment\Http;

use App\Authorization\Assignment\Query\ContextRoleAssignmentFilter;
use Illuminate\Http\Request;

class ContextRoleAssignmentFilterFactory
{
    public function fromRequest(
        Request $request
    ): ContextRoleAssignmentFilter {
        return new ContextRoleAssignmentFilter(
            authIdentityId: $this->nullablePositiveInteger(
                $request->query('auth_identity_id')
            ),
            companyId: $this->nullablePositiveInteger(
                $request->query('company_id')
            ),
            businessUnitId: $this->nullablePositiveInteger(
                $request->query('business_unit_id')
            ),
            systemId: $this->nullablePositiveInteger(
                $request->query('system_id')
            ),
            roleName: $this->nullableString(
                $request->query('role')
            ),
            status: $this->nullableString(
                $request->query('status')
            ),
            perPage: $this->positiveInteger(
                $request->query('per_page'),
                25
            ),
            page: $this->positiveInteger(
                $request->query('page'),
                1
            ),
        );
    }

    private function nullablePositiveInteger(
        mixed $value
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        if (
            !is_int($value)
            && !(is_string($value) && ctype_digit($value))
        ) {
            return 0;
        }

        return (int) $value;
    }

    private function positiveInteger(
        mixed $value,
        int $default
    ): int {
        if ($value === null || $value === '') {
            return $default;
        }

        if (
            !is_int($value)
            && !(is_string($value) && ctype_digit($value))
        ) {
            return 0;
        }

        return (int) $value;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }
}
