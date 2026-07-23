<?php

namespace App\Authorization\Membership;

use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class ApprovedContextMembershipProvisioner
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly IdentityMembershipService $memberships,
    ) {
    }

    public function provision(
        int $authIdentityId,
        int $sourceBusinessUnitId
    ): IdentityMembership {
        if ($authIdentityId < 1) {
            throw new RuntimeException(
                'The IAM identity is invalid.'
            );
        }

        if ($sourceBusinessUnitId < 1) {
            throw new RuntimeException(
                'The approved business unit is invalid.'
            );
        }

        $businessUnit = $this->database
            ->table('access_business_units')
            ->where(
                'external_key',
                "1office-bu:{$sourceBusinessUnitId}"
            )
            ->where('status', 'active')
            ->first([
                'id',
                'company_id',
            ]);

        if (!$businessUnit) {
            throw new RuntimeException(
                'The approved business unit is not mapped '
                . 'into the IAM catalogue.'
            );
        }

        $systemId = $this->database
            ->table('access_systems')
            ->where('slug', 'iam')
            ->where('status', 'active')
            ->value('id');

        if ($systemId === null) {
            throw new RuntimeException(
                'The IAM Platform system is unavailable.'
            );
        }

        $membership = new IdentityMembership(
            authIdentityId: $authIdentityId,
            companyId: (int) $businessUnit->company_id,
            businessUnitId: (int) $businessUnit->id,
            systemId: (int) $systemId,
        );

        $this->memberships->grant($membership);

        return $membership;
    }
}
