<?php

namespace App\Authorization\Assignment;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ContextRoleAssignment
{
    public function __construct(
        public int $authIdentityId,
        public string $roleName,
        public ?int $companyId = null,
        public ?int $businessUnitId = null,
        public ?int $systemId = null,
        public ?DateTimeImmutable $validFrom = null,
        public ?DateTimeImmutable $validUntil = null,
    ) {
        if ($authIdentityId < 1) {
            throw new InvalidArgumentException(
                'authIdentityId must be positive.'
            );
        }

        if (trim($roleName) === '') {
            throw new InvalidArgumentException(
                'roleName cannot be empty.'
            );
        }

        foreach ([
            'companyId' => $companyId,
            'businessUnitId' => $businessUnitId,
            'systemId' => $systemId,
        ] as $field => $value) {
            if ($value !== null && $value < 1) {
                throw new InvalidArgumentException(
                    "{$field} must be null or positive."
                );
            }
        }

        if (
            $businessUnitId !== null
            && $companyId === null
        ) {
            throw new InvalidArgumentException(
                'A business unit assignment requires a company.'
            );
        }

        if (
            $validFrom !== null
            && $validUntil !== null
            && $validUntil <= $validFrom
        ) {
            throw new InvalidArgumentException(
                'validUntil must be later than validFrom.'
            );
        }
    }

    public function normalizedRoleName(): string
    {
        return strtolower(trim($this->roleName));
    }

    /**
     * @return array<string, int|string|null>
     */
    public function context(): array
    {
        return [
            'auth_identity_id' => $this->authIdentityId,
            'role_name' => $this->normalizedRoleName(),
            'company_id' => $this->companyId,
            'business_unit_id' => $this->businessUnitId,
            'system_id' => $this->systemId,
        ];
    }
}
