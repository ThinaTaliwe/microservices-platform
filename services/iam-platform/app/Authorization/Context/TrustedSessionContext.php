<?php

namespace App\Authorization\Context;

use InvalidArgumentException;

final readonly class TrustedSessionContext
{
    public function __construct(
        public int $authIdentityId,
        public int $companyId,
        public int $businessUnitId,
        public int $systemId,
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
    }

    /**
     * @return array<string, int>
     */
    public function sessionValues(): array
    {
        return [
            'auth_identity_id' => $this->authIdentityId,
            'active_company_id' => $this->companyId,
            'active_bu_id' => $this->businessUnitId,
            'active_system_id' => $this->systemId,
        ];
    }
}
