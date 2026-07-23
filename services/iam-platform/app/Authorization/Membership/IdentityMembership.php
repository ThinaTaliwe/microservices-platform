<?php

namespace App\Authorization\Membership;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class IdentityMembership
{
    public function __construct(
        public int $authIdentityId,
        public int $companyId,
        public int $businessUnitId,
        public int $systemId,
        public ?DateTimeImmutable $validFrom = null,
        public ?DateTimeImmutable $validUntil = null,
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
}
