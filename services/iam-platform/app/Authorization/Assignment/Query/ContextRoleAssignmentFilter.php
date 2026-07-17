<?php

namespace App\Authorization\Assignment\Query;

use InvalidArgumentException;

final readonly class ContextRoleAssignmentFilter
{
    private const ALLOWED_STATUSES = [
        'active',
        'revoked',
    ];

    public function __construct(
        public ?int $authIdentityId = null,
        public ?int $companyId = null,
        public ?int $businessUnitId = null,
        public ?int $systemId = null,
        public ?string $roleName = null,
        public ?string $status = null,
        public int $perPage = 25,
        public int $page = 1,
    ) {
        foreach ([
            'authIdentityId' => $authIdentityId,
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

        if ($businessUnitId !== null && $companyId === null) {
            throw new InvalidArgumentException(
                'A business-unit filter requires a company filter.'
            );
        }

        if ($perPage < 1 || $perPage > 100) {
            throw new InvalidArgumentException(
                'perPage must be between 1 and 100.'
            );
        }

        if ($page < 1) {
            throw new InvalidArgumentException(
                'page must be positive.'
            );
        }

        if (
            $status !== null
            && !in_array(
                strtolower(trim($status)),
                self::ALLOWED_STATUSES,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported assignment status.'
            );
        }
    }

    public function normalizedRoleName(): ?string
    {
        if ($this->roleName === null) {
            return null;
        }

        $roleName = strtolower(trim($this->roleName));

        return $roleName === ''
            ? null
            : $roleName;
    }

    public function normalizedStatus(): ?string
    {
        return $this->status === null
            ? null
            : strtolower(trim($this->status));
    }
}
