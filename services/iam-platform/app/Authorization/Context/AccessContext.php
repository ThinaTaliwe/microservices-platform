<?php

namespace App\Authorization\Context;

use InvalidArgumentException;

final readonly class AccessContext
{
    public function __construct(
        public int $authIdentityId,
        public int $companyId,
        public int $businessUnitId,
        public int $systemId,
        public ?int $componentId = null,
    ) {
        foreach ([
            'authIdentityId' => $authIdentityId,
            'companyId' => $companyId,
            'businessUnitId' => $businessUnitId,
            'systemId' => $systemId,
        ] as $field => $value) {
            if ($value < 1) {
                throw new InvalidArgumentException(
                    "{$field} must be a positive integer."
                );
            }
        }

        if ($componentId !== null && $componentId < 1) {
            throw new InvalidArgumentException(
                'componentId must be null or a positive integer.'
            );
        }
    }

    /**
     * @return array<string, int|null>
     */
    public function toArray(): array
    {
        return [
            'auth_identity_id' => $this->authIdentityId,
            'company_id' => $this->companyId,
            'business_unit_id' => $this->businessUnitId,
            'system_id' => $this->systemId,
            'component_id' => $this->componentId,
        ];
    }

    public function scopeCacheKey(): string
    {
        return implode(':', [
            'iam-v2',
            'access-scope',
            $this->authIdentityId,
            $this->companyId,
            $this->businessUnitId,
            $this->systemId,
        ]);
    }

    public function cacheKey(): string
    {
        return implode(':', [
            $this->scopeCacheKey(),
            'component',
            $this->componentId ?? 'none',
        ]);
    }
}
