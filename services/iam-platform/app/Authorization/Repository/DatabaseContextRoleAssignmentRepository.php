<?php

namespace App\Authorization\Repository;

use App\Authorization\Assignment\ContextRoleAssignment;
use App\Authorization\Assignment\ContextRoleAssignmentException;
use App\Authorization\Contracts\ContextRoleAssignmentRepository;
use DateTimeInterface;
use Illuminate\Database\ConnectionInterface;

class DatabaseContextRoleAssignmentRepository implements
    ContextRoleAssignmentRepository
{
    public function __construct(
        private readonly ConnectionInterface $database
    ) {
    }

    public function assign(
        ContextRoleAssignment $assignment
    ): void {
        $this->database->transaction(
            function () use ($assignment): void {
                $roleId = $this->validatedRoleId(
                    $assignment
                );

                $this->validateContext($assignment);

                $now = now()->format('Y-m-d H:i:s');

                /*
                 * The unique index uses generated scope columns that
                 * normalize nullable company, BU and system IDs to zero.
                 * ON DUPLICATE KEY therefore works for global contexts too.
                 */
                $this->database->statement(
                    <<<'SQL'
INSERT INTO access_role_contexts (
    auth_identity_id,
    role_id,
    company_id,
    business_unit_id,
    system_id,
    status,
    valid_from,
    valid_until,
    created_at,
    updated_at
)
VALUES (?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    status = 'active',
    valid_from = VALUES(valid_from),
    valid_until = VALUES(valid_until),
    updated_at = VALUES(updated_at)
SQL,
                    [
                        $assignment->authIdentityId,
                        $roleId,
                        $assignment->companyId,
                        $assignment->businessUnitId,
                        $assignment->systemId,
                        $this->formatDate(
                            $assignment->validFrom
                        ),
                        $this->formatDate(
                            $assignment->validUntil
                        ),
                        $now,
                        $now,
                    ]
                );
            },
            3
        );
    }

    public function revoke(
        ContextRoleAssignment $assignment
    ): bool {
        return $this->database->transaction(
            function () use ($assignment): bool {
                $roleId = $this->roleId(
                    $assignment->normalizedRoleName()
                );

                if ($roleId === null) {
                    return false;
                }

                $query = $this->database
                    ->table('access_role_contexts')
                    ->where(
                        'auth_identity_id',
                        $assignment->authIdentityId
                    )
                    ->where('role_id', $roleId)
                    ->where(
                        'company_scope_id',
                        $assignment->companyId ?? 0
                    )
                    ->where(
                        'business_unit_scope_id',
                        $assignment->businessUnitId ?? 0
                    )
                    ->where(
                        'system_scope_id',
                        $assignment->systemId ?? 0
                    )
                    ->where('status', 'active');

                return $query->update([
                    'status' => 'revoked',
                    'updated_at' => now(),
                ]) > 0;
            },
            3
        );
    }

    private function validatedRoleId(
        ContextRoleAssignment $assignment
    ): int {
        if (
            !$this->database
                ->table('auth_identities')
                ->where(
                    'id',
                    $assignment->authIdentityId
                )
                ->exists()
        ) {
            throw new ContextRoleAssignmentException(
                'The IAM identity does not exist.'
            );
        }

        $roleId = $this->roleId(
            $assignment->normalizedRoleName()
        );

        if ($roleId === null) {
            throw new ContextRoleAssignmentException(
                'The requested IAM role does not exist.'
            );
        }

        return $roleId;
    }

    private function validateContext(
        ContextRoleAssignment $assignment
    ): void {
        if ($assignment->companyId !== null) {
            $companyExists = $this->database
                ->table('access_companies')
                ->where('id', $assignment->companyId)
                ->where('status', 'active')
                ->exists();

            if (!$companyExists) {
                throw new ContextRoleAssignmentException(
                    'The selected company is unavailable.'
                );
            }
        }

        if ($assignment->businessUnitId !== null) {
            $businessUnitExists = $this->database
                ->table('access_business_units')
                ->where(
                    'id',
                    $assignment->businessUnitId
                )
                ->where(
                    'company_id',
                    $assignment->companyId
                )
                ->where('status', 'active')
                ->exists();

            if (!$businessUnitExists) {
                throw new ContextRoleAssignmentException(
                    'The selected business unit is unavailable '
                    . 'or does not belong to the company.'
                );
            }
        }

        if ($assignment->systemId !== null) {
            $systemExists = $this->database
                ->table('access_systems')
                ->where('id', $assignment->systemId)
                ->where('status', 'active')
                ->exists();

            if (!$systemExists) {
                throw new ContextRoleAssignmentException(
                    'The selected system is unavailable.'
                );
            }
        }
    }

    private function roleId(
        string $roleName
    ): ?int {
        $roleId = $this->database
            ->table('roles')
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->value('id');

        return $roleId === null
            ? null
            : (int) $roleId;
    }

    private function formatDate(
        ?DateTimeInterface $date
    ): ?string {
        return $date?->format('Y-m-d H:i:s');
    }
}
