<?php

namespace App\Authorization\Assignment\Http;

use App\Authorization\Assignment\ContextRoleAssignment;
use DateTimeImmutable;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContextRoleAssignmentFactory
{
    public function fromRequest(
        Request $request
    ): ContextRoleAssignment {
        return new ContextRoleAssignment(
            authIdentityId: $this->requiredPositiveInteger(
                $request->input('auth_identity_id'),
                'auth_identity_id'
            ),
            roleName: $this->requiredString(
                $request->input('role'),
                'role'
            ),
            companyId: $this->nullablePositiveInteger(
                $request->input('company_id'),
                'company_id'
            ),
            businessUnitId: $this->nullablePositiveInteger(
                $request->input('business_unit_id'),
                'business_unit_id'
            ),
            systemId: $this->nullablePositiveInteger(
                $request->input('system_id'),
                'system_id'
            ),
            validFrom: $this->nullableDate(
                $request->input('valid_from'),
                'valid_from'
            ),
            validUntil: $this->nullableDate(
                $request->input('valid_until'),
                'valid_until'
            ),
        );
    }

    private function requiredPositiveInteger(
        mixed $value,
        string $field
    ): int {
        $parsed = $this->parsePositiveInteger(
            $value,
            $field
        );

        if ($parsed === null) {
            throw new InvalidArgumentException(
                "{$field} is required."
            );
        }

        return $parsed;
    }

    private function nullablePositiveInteger(
        mixed $value,
        string $field
    ): ?int {
        return $this->parsePositiveInteger(
            $value,
            $field
        );
    }

    private function parsePositiveInteger(
        mixed $value,
        string $field
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        if (
            !is_int($value)
            && !(
                is_string($value)
                && ctype_digit($value)
            )
        ) {
            throw new InvalidArgumentException(
                "{$field} must be a positive integer."
            );
        }

        $parsed = (int) $value;

        if ($parsed < 1) {
            throw new InvalidArgumentException(
                "{$field} must be a positive integer."
            );
        }

        return $parsed;
    }

    private function requiredString(
        mixed $value,
        string $field
    ): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                "{$field} is required."
            );
        }

        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException(
                "{$field} is required."
            );
        }

        if (mb_strlen($value) > 100) {
            throw new InvalidArgumentException(
                "{$field} is too long."
            );
        }

        return $value;
    }

    private function nullableDate(
        mixed $value,
        string $field
    ): ?DateTimeImmutable {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(
                "{$field} must be a valid date."
            );
        }

        $date = DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i',
            $value
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || (
                is_array($errors)
                && (
                    $errors['warning_count'] > 0
                    || $errors['error_count'] > 0
                )
            )
        ) {
            throw new InvalidArgumentException(
                "{$field} must use YYYY-MM-DDTHH:MM."
            );
        }

        return $date;
    }
}
