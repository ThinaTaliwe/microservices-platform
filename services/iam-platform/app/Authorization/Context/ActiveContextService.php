<?php

namespace App\Authorization\Context;

use App\Authorization\Contracts\AuthorizationCacheInvalidator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use RuntimeException;

class ActiveContextService
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly AuthorizationCacheInvalidator $cache,
    ) {
    }

    /**
     * @return list<array{
     *     company_id: int,
     *     company_name: string,
     *     business_unit_id: int,
     *     business_unit_name: string,
     *     system_id: int,
     *     system_name: string
     * }>
     */
    public function available(int $authIdentityId): array
    {
        if ($authIdentityId < 1) {
            throw new RuntimeException(
                'The IAM identity is invalid.'
            );
        }

        return $this->database
            ->table(
                'access_identity_business_units AS bu_membership'
            )
            ->join(
                'access_business_units AS business_unit',
                'business_unit.id',
                '=',
                'bu_membership.business_unit_id'
            )
            ->join(
                'access_companies AS company',
                'company.id',
                '=',
                'business_unit.company_id'
            )
            ->join(
                'access_identity_companies AS company_membership',
                function ($join) use ($authIdentityId): void {
                    $join
                        ->on(
                            'company_membership.company_id',
                            '=',
                            'company.id'
                        )
                        ->where(
                            'company_membership.auth_identity_id',
                            '=',
                            $authIdentityId
                        );
                }
            )
            ->join(
                'access_identity_systems AS system_membership',
                'system_membership.auth_identity_id',
                '=',
                'bu_membership.auth_identity_id'
            )
            ->join(
                'access_systems AS system_catalog',
                'system_catalog.id',
                '=',
                'system_membership.system_id'
            )
            ->where(
                'bu_membership.auth_identity_id',
                $authIdentityId
            )
            ->where('bu_membership.status', 'active')
            ->where('company_membership.status', 'active')
            ->where('system_membership.status', 'active')
            ->where('business_unit.status', 'active')
            ->where('company.status', 'active')
            ->where('system_catalog.status', 'active')
            ->orderBy('company.name')
            ->orderBy('business_unit.name')
            ->orderBy('system_catalog.name')
            ->get([
                'company.id AS company_id',
                'company.name AS company_name',
                'business_unit.id AS business_unit_id',
                'business_unit.name AS business_unit_name',
                'system_catalog.id AS system_id',
                'system_catalog.name AS system_name',
            ])
            ->map(
                static fn ($row): array => [
                    'company_id' => (int) $row->company_id,
                    'company_name' =>
                        (string) $row->company_name,
                    'business_unit_id' =>
                        (int) $row->business_unit_id,
                    'business_unit_name' =>
                        (string) $row->business_unit_name,
                    'system_id' => (int) $row->system_id,
                    'system_name' =>
                        (string) $row->system_name,
                ]
            )
            ->values()
            ->all();
    }

    public function switch(
        Request $request,
        int $companyId,
        int $businessUnitId,
        int $systemId
    ): void {
        $authIdentityId = (int) $request
            ->session()
            ->get('auth_identity_id');

        $allowed = collect(
            $this->available($authIdentityId)
        )->contains(
            static fn (array $context): bool =>
                $context['company_id'] === $companyId
                && $context['business_unit_id']
                    === $businessUnitId
                && $context['system_id'] === $systemId
        );

        if (!$allowed) {
            throw new RuntimeException(
                'The selected IAM context is unavailable.'
            );
        }

        $request->session()->put([
            'active_company_id' => $companyId,
            'active_bu_id' => $businessUnitId,
            'active_system_id' => $systemId,
        ]);

        $request->session()->regenerate();

        $this->cache->invalidateIdentity(
            $authIdentityId
        );
    }
}
