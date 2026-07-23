<?php

namespace App\Authorization\Repository;

use App\Authorization\Contracts\IdentityMembershipRepository;
use App\Authorization\Membership\IdentityMembership;
use DateTimeInterface;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class DatabaseIdentityMembershipRepository implements
    IdentityMembershipRepository
{
    public function __construct(
        private readonly ConnectionInterface $database
    ) {
    }

    public function grant(
        IdentityMembership $membership
    ): void {
        $this->database->transaction(
            function () use ($membership): void {
                $this->validate($membership);

                $now = now();

                $values = [
                    'status' => 'active',
                    'valid_from' => $this->formatDate(
                        $membership->validFrom
                    ),
                    'valid_until' => $this->formatDate(
                        $membership->validUntil
                    ),
                    'updated_at' => $now,
                ];

                $this->database
                    ->table('access_identity_companies')
                    ->updateOrInsert(
                        [
                            'auth_identity_id' =>
                                $membership->authIdentityId,
                            'company_id' =>
                                $membership->companyId,
                        ],
                        $values + [
                            'created_at' => $now,
                        ]
                    );

                $this->database
                    ->table(
                        'access_identity_business_units'
                    )
                    ->updateOrInsert(
                        [
                            'auth_identity_id' =>
                                $membership->authIdentityId,
                            'business_unit_id' =>
                                $membership->businessUnitId,
                        ],
                        $values + [
                            'created_at' => $now,
                        ]
                    );

                $this->database
                    ->table('access_identity_systems')
                    ->updateOrInsert(
                        [
                            'auth_identity_id' =>
                                $membership->authIdentityId,
                            'system_id' =>
                                $membership->systemId,
                        ],
                        $values + [
                            'created_at' => $now,
                        ]
                    );
            },
            3
        );
    }

    public function revoke(
        IdentityMembership $membership
    ): bool {
        return $this->database->transaction(
            function () use ($membership): bool {
                $now = now();

                $businessUnitRevoked = $this->database
                    ->table(
                        'access_identity_business_units'
                    )
                    ->where(
                        'auth_identity_id',
                        $membership->authIdentityId
                    )
                    ->where(
                        'business_unit_id',
                        $membership->businessUnitId
                    )
                    ->where('status', 'active')
                    ->update([
                        'status' => 'revoked',
                        'updated_at' => $now,
                    ]) > 0;

                if (!$businessUnitRevoked) {
                    return false;
                }

                /*
                 * Company membership remains active while the identity
                 * still has another active BU in that company.
                 */
                $hasOtherBusinessUnit = $this->database
                    ->table(
                        'access_identity_business_units AS membership'
                    )
                    ->join(
                        'access_business_units AS business_unit',
                        'business_unit.id',
                        '=',
                        'membership.business_unit_id'
                    )
                    ->where(
                        'membership.auth_identity_id',
                        $membership->authIdentityId
                    )
                    ->where(
                        'business_unit.company_id',
                        $membership->companyId
                    )
                    ->where(
                        'membership.status',
                        'active'
                    )
                    ->exists();

                if (!$hasOtherBusinessUnit) {
                    $this->database
                        ->table(
                            'access_identity_companies'
                        )
                        ->where(
                            'auth_identity_id',
                            $membership->authIdentityId
                        )
                        ->where(
                            'company_id',
                            $membership->companyId
                        )
                        ->where('status', 'active')
                        ->update([
                            'status' => 'revoked',
                            'updated_at' => $now,
                        ]);
                }

                /*
                 * System membership is deliberately retained. A system
                 * can be shared across several companies and BUs.
                 */
                return true;
            },
            3
        );
    }

    private function validate(
        IdentityMembership $membership
    ): void {
        $identityExists = $this->database
            ->table('auth_identities')
            ->where('id', $membership->authIdentityId)
            ->where('status', 'active')
            ->exists();

        if (!$identityExists) {
            throw new RuntimeException(
                'The active IAM identity does not exist.'
            );
        }

        $companyExists = $this->database
            ->table('access_companies')
            ->where('id', $membership->companyId)
            ->where('status', 'active')
            ->exists();

        if (!$companyExists) {
            throw new RuntimeException(
                'The selected IAM company is unavailable.'
            );
        }

        $businessUnitExists = $this->database
            ->table('access_business_units')
            ->where('id', $membership->businessUnitId)
            ->where('company_id', $membership->companyId)
            ->where('status', 'active')
            ->exists();

        if (!$businessUnitExists) {
            throw new RuntimeException(
                'The selected business unit is unavailable '
                . 'or does not belong to the company.'
            );
        }

        $systemExists = $this->database
            ->table('access_systems')
            ->where('id', $membership->systemId)
            ->where('status', 'active')
            ->exists();

        if (!$systemExists) {
            throw new RuntimeException(
                'The selected IAM system is unavailable.'
            );
        }
    }

    private function formatDate(
        ?DateTimeInterface $date
    ): ?string {
        return $date?->format('Y-m-d H:i:s');
    }
}
