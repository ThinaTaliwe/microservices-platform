<?php

namespace App\Authorization\Bootstrap;

use App\Authorization\Assignment\ContextRoleAssignment;
use App\Authorization\Assignment\ContextRoleAssignmentService;
use App\Authorization\Catalog\RoleCatalog;
use App\Authorization\Membership\IdentityMembership;
use App\Authorization\Membership\IdentityMembershipService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class BootstrapAdministratorService
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly IdentityMembershipService $memberships,
        private readonly ContextRoleAssignmentService $roles,
    ) {
    }

    public function isBootstrapAdministrator(
        string $email
    ): bool {
        $normalizedEmail = strtolower(trim($email));

        if ($normalizedEmail === '') {
            return false;
        }

        $configuredEmails = collect(
            config('bootstrap-admins.emails', [])
        )
            ->filter(fn ($value): bool => is_string($value))
            ->map(
                fn (string $value): string =>
                    strtolower(trim($value))
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        return in_array(
            $normalizedEmail,
            $configuredEmails,
            true
        );
    }

    public function isBootstrapIdentity(
        int $authIdentityId
    ): bool {
        if ($authIdentityId < 1) {
            return false;
        }

        $encryptedEmail = $this->database
            ->table('auth_identities')
            ->where('id', $authIdentityId)
            ->where('status', 'active')
            ->value('email_encrypted');

        if (
            !is_string($encryptedEmail)
            || trim($encryptedEmail) === ''
        ) {
            return false;
        }

        try {
            $email = Crypt::decryptString(
                $encryptedEmail
            );
        } catch (\Throwable) {
            return false;
        }

        return $this->isBootstrapAdministrator(
            $email
        );
    }

    /**
     * @return array{
     *     auth_identity_id: int,
     *     company_count: int,
     *     business_unit_count: int,
     *     system_count: int,
     *     context_count: int,
     *     role: string
     * }
     */
    public function provision(
        int $authIdentityId,
        string $email
    ): array {
        if ($authIdentityId < 1) {
            throw new RuntimeException(
                'The bootstrap IAM identity is invalid.'
            );
        }

        if (!$this->isBootstrapAdministrator($email)) {
            throw new RuntimeException(
                'The email is not a configured bootstrap administrator.'
            );
        }

        $identityExists = $this->database
            ->table('auth_identities')
            ->where('id', $authIdentityId)
            ->where('status', 'active')
            ->exists();

        if (!$identityExists) {
            throw new RuntimeException(
                'The active bootstrap IAM identity does not exist.'
            );
        }

        $businessUnits = $this->database
            ->table(
                'access_business_units AS business_unit'
            )
            ->join(
                'access_companies AS company',
                'company.id',
                '=',
                'business_unit.company_id'
            )
            ->where('business_unit.status', 'active')
            ->where('company.status', 'active')
            ->orderBy('business_unit.company_id')
            ->orderBy('business_unit.id')
            ->get([
                'business_unit.id',
                'business_unit.company_id',
            ]);

        $systems = $this->database
            ->table('access_systems')
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id']);

        if ($businessUnits->isEmpty()) {
            throw new RuntimeException(
                'No active IAM business units are available.'
            );
        }

        if ($systems->isEmpty()) {
            throw new RuntimeException(
                'No active IAM systems are available.'
            );
        }

        $this->database->transaction(
            function () use (
                $authIdentityId,
                $businessUnits,
                $systems
            ): void {
                foreach ($businessUnits as $businessUnit) {
                    foreach ($systems as $system) {
                        $this->memberships->grant(
                            new IdentityMembership(
                                authIdentityId:
                                    $authIdentityId,
                                companyId:
                                    (int) $businessUnit
                                        ->company_id,
                                businessUnitId:
                                    (int) $businessUnit->id,
                                systemId:
                                    (int) $system->id,
                            )
                        );
                    }
                }

                $this->roles->assign(
                    new ContextRoleAssignment(
                        authIdentityId:
                            $authIdentityId,
                        roleName:
                            RoleCatalog::PLATFORM_SUPER_ADMIN,
                        companyId: null,
                        businessUnitId: null,
                        systemId: null,
                    )
                );
            },
            3
        );

        return [
            'auth_identity_id' => $authIdentityId,
            'company_count' => $businessUnits
                ->pluck('company_id')
                ->unique()
                ->count(),
            'business_unit_count' =>
                $businessUnits->count(),
            'system_count' => $systems->count(),
            'context_count' =>
                $businessUnits->count()
                * $systems->count(),
            'role' =>
                RoleCatalog::PLATFORM_SUPER_ADMIN,
        ];
    }
}
