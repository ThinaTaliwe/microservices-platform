<?php

namespace App\Authorization\Context;

use InvalidArgumentException;

final readonly class TrustedRequestContext
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

    public function toAccessContext(): AccessContext
    {
        return new AccessContext(
            authIdentityId: $this->authIdentityId,
            companyId: $this->companyId,
            businessUnitId: $this->businessUnitId,
            systemId: $this->systemId,
            componentId: $this->componentId,
        );
    }
}
