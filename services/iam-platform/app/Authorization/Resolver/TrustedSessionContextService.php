<?php

namespace App\Authorization\Resolver;

use App\Authorization\Bootstrap\BootstrapAdministratorService;
use App\Authorization\Context\TrustedSessionContext;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class TrustedSessionContextService
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly BootstrapAdministratorService $bootstrapAdministrators,
    ) {
    }

    public function resolve(
        int $authIdentityId,
        int $loginAttemptId
    ): TrustedSessionContext {
        $this->validateIdentifiers(
            $authIdentityId,
            $loginAttemptId
        );

        $this->assertActiveIdentity(
            $authIdentityId
        );

        $approvedContext =
            $this->resolveApprovedContext(
                $authIdentityId,
                $loginAttemptId
            );

        if ($approvedContext !== null) {
            return $approvedContext;
        }

        if (
            !$this->bootstrapAdministrators
                ->isBootstrapIdentity(
                    $authIdentityId
                )
        ) {
            throw new RuntimeException(
                'An approved login context was not found.'
            );
        }

        return $this->resolveBootstrapContext(
            $authIdentityId
        );
    }

    public function store(
        Request $request,
        TrustedSessionContext $context
    ): void {
        $session = $request->session();

        $session->put(
            $context->sessionValues()
        );

        $session->regenerate();
    }

    private function validateIdentifiers(
        int $authIdentityId,
        int $loginAttemptId
    ): void {
        if ($authIdentityId < 1) {
            throw new InvalidArgumentException(
                'authIdentityId must be positive.'
            );
        }

        if ($loginAttemptId < 1) {
            throw new InvalidArgumentException(
                'loginAttemptId must be positive.'
            );
        }
    }

    private function assertActiveIdentity(
        int $authIdentityId
    ): void {
        $identityExists = $this->database
            ->table('auth_identities')
            ->where('id', $authIdentityId)
            ->where('status', 'active')
            ->exists();

        if (!$identityExists) {
            throw new RuntimeException(
                'The active IAM identity does not exist.'
            );
        }
    }

    private function resolveApprovedContext(
        int $authIdentityId,
        int $loginAttemptId
    ): ?TrustedSessionContext {
        $approvalQuery = $this->database
            ->table('auth_pending_approvals')
            ->where(
                'auth_identity_id',
                $authIdentityId
            )
            ->where('status', 'approved')
            ->whereNotNull('approved_bu_id');

        $approval = (clone $approvalQuery)
            ->where(
                'login_attempt_id',
                $loginAttemptId
            )
            ->orderByDesc('id')
            ->first([
                'approved_bu_id',
            ]);

        /*
         * Trusted-device logins do not create another supervisor
         * approval. Retain the identity's latest approved context.
         */
        $approval ??= $approvalQuery
            ->orderByDesc('id')
            ->first([
                'approved_bu_id',
            ]);

        if (!$approval) {
            return null;
        }

        $sourceBusinessUnitId =
            (int) $approval->approved_bu_id;

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

        $companyId =
            (int) $businessUnit->company_id;

        $companyExists = $this->database
            ->table('access_companies')
            ->where('id', $companyId)
            ->where('status', 'active')
            ->exists();

        if (!$companyExists) {
            throw new RuntimeException(
                'The IAM company for the approved '
                . 'business unit is unavailable.'
            );
        }

        $systemId = $this->iamSystemId();

        return new TrustedSessionContext(
            authIdentityId: $authIdentityId,
            companyId: $companyId,
            businessUnitId:
                (int) $businessUnit->id,
            systemId: $systemId,
        );
    }

    private function resolveBootstrapContext(
        int $authIdentityId
    ): TrustedSessionContext {
        $context = $this->database
            ->table(
                'access_identity_business_units '
                . 'AS bu_membership'
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
                'access_identity_companies '
                . 'AS company_membership',
                function ($join) use (
                    $authIdentityId
                ): void {
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
                'access_identity_systems '
                . 'AS system_membership',
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
            ->where(
                'bu_membership.status',
                'active'
            )
            ->where(
                'company_membership.status',
                'active'
            )
            ->where(
                'system_membership.status',
                'active'
            )
            ->where(
                'business_unit.status',
                'active'
            )
            ->where('company.status', 'active')
            ->where(
                'system_catalog.status',
                'active'
            )
            ->orderBy('company.id')
            ->orderBy('business_unit.id')
            ->orderBy('system_catalog.id')
            ->first([
                'company.id AS company_id',
                'business_unit.id '
                    . 'AS business_unit_id',
                'system_catalog.id AS system_id',
            ]);

        if (!$context) {
            throw new RuntimeException(
                'The bootstrap administrator has no '
                . 'active IAM membership context.'
            );
        }

        return new TrustedSessionContext(
            authIdentityId: $authIdentityId,
            companyId:
                (int) $context->company_id,
            businessUnitId:
                (int) $context->business_unit_id,
            systemId:
                (int) $context->system_id,
        );
    }

    private function iamSystemId(): int
    {
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

        return (int) $systemId;
    }
}
