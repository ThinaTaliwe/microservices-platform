<?php

namespace App\Authorization\Resolver;

use App\Authorization\Context\TrustedSessionContext;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class TrustedSessionContextService
{
    public function __construct(
        private readonly ConnectionInterface $database
    ) {
    }

    public function resolve(
        int $authIdentityId,
        int $loginAttemptId
    ): TrustedSessionContext {
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

        $approvalQuery = $this->database
            ->table('auth_pending_approvals')
            ->where('auth_identity_id', $authIdentityId)
            ->where('status', 'approved')
            ->whereNotNull('approved_bu_id');

        $approval = (clone $approvalQuery)
            ->where('login_attempt_id', $loginAttemptId)
            ->orderByDesc('id')
            ->first([
                'approved_bu_id',
            ]);

        /*
         * Trusted-device logins do not create another supervisor
         * approval. In that case, retain the identity's most recently
         * approved business-unit context.
         */
        $approval ??= $approvalQuery
            ->orderByDesc('id')
            ->first([
                'approved_bu_id',
            ]);

        if (!$approval) {
            throw new RuntimeException(
                'An approved login context was not found.'
            );
        }

        $sourceBusinessUnitId = (int) $approval->approved_bu_id;

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

        $businessUnitId = (int) $businessUnit->id;
        $companyId = (int) $businessUnit->company_id;

        $companyExists = $this->database
            ->table('access_companies')
            ->where('id', $companyId)
            ->where('status', 'active')
            ->exists();

        if (!$companyExists) {
            throw new RuntimeException(
                'The IAM company for the approved business unit '
                . 'is unavailable.'
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

        $systemId = (int) $systemId;

        return new TrustedSessionContext(
            authIdentityId: $authIdentityId,
            companyId: $companyId,
            businessUnitId: $businessUnitId,
            systemId: $systemId,
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
}
