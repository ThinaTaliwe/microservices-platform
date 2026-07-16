<?php

namespace App\Authorization\Repository;

use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Context\AccessContext;
use App\Authorization\Snapshot\AccessSnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class DatabaseContextAccessRepository implements
    ContextAccessRepository
{
    /**
     * Request-lifetime base authorization graphs.
     *
     * The component does not affect memberships, roles, or permissions.
     *
     * @var array<string, AccessSnapshot>
     */
    private array $baseSnapshots = [];

    public function __construct(
        private readonly ConnectionInterface $database
    ) {
    }

    public function snapshot(
        AccessContext $context
    ): AccessSnapshot {
        $base = $this->baseSnapshot($context);

        if (
            !$base->membershipsAreValid()
            || $context->componentId === null
        ) {
            return $base;
        }

        return new AccessSnapshot(
            companyAccess: $base->companyAccess,
            businessUnitAccess:
                $base->businessUnitAccess,
            systemAccess: $base->systemAccess,
            roles: $base->roles,
            permissions: $base->permissions,
            overrides: $this->overrides($context),
        );
    }

    private function baseSnapshot(
        AccessContext $context
    ): AccessSnapshot {
        $key = $context->scopeCacheKey();

        return $this->baseSnapshots[$key]
            ??= $this->loadBaseSnapshot($context);
    }

    private function loadBaseSnapshot(
        AccessContext $context
    ): AccessSnapshot {
        $memberships = $this->memberships($context);

        if (
            !$memberships['company']
            || !$memberships['business_unit']
            || !$memberships['system']
        ) {
            return AccessSnapshot::denied();
        }

        $authorization = $this->authorizationRows(
            $context
        );

        return new AccessSnapshot(
            companyAccess: true,
            businessUnitAccess: true,
            systemAccess: true,
            roles: $authorization['roles'],
            permissions:
                $authorization['permissions'],
            overrides: [],
        );
    }

    /**
     * @return array{
     *     company: bool,
     *     business_unit: bool,
     *     system: bool
     * }
     */
    private function memberships(
        AccessContext $context
    ): array {
        $row = $this->database->selectOne(
            <<<'SQL'
SELECT
    EXISTS (
        SELECT 1
        FROM access_identity_companies AS aic
        INNER JOIN access_companies AS company
            ON company.id = aic.company_id
        WHERE aic.auth_identity_id = ?
          AND aic.company_id = ?
          AND aic.status = 'active'
          AND company.status = 'active'
          AND (
              aic.valid_from IS NULL
              OR aic.valid_from <= CURRENT_TIMESTAMP
          )
          AND (
              aic.valid_until IS NULL
              OR aic.valid_until >= CURRENT_TIMESTAMP
          )
    ) AS company_access,

    EXISTS (
        SELECT 1
        FROM access_identity_business_units AS aibu
        INNER JOIN access_business_units AS bu
            ON bu.id = aibu.business_unit_id
        WHERE aibu.auth_identity_id = ?
          AND aibu.business_unit_id = ?
          AND bu.company_id = ?
          AND aibu.status = 'active'
          AND bu.status = 'active'
          AND (
              aibu.valid_from IS NULL
              OR aibu.valid_from <= CURRENT_TIMESTAMP
          )
          AND (
              aibu.valid_until IS NULL
              OR aibu.valid_until >= CURRENT_TIMESTAMP
          )
    ) AS business_unit_access,

    EXISTS (
        SELECT 1
        FROM access_identity_systems AS ais
        INNER JOIN access_systems AS system_catalog
            ON system_catalog.id = ais.system_id
        WHERE ais.auth_identity_id = ?
          AND ais.system_id = ?
          AND ais.status = 'active'
          AND system_catalog.status = 'active'
          AND (
              ais.valid_from IS NULL
              OR ais.valid_from <= CURRENT_TIMESTAMP
          )
          AND (
              ais.valid_until IS NULL
              OR ais.valid_until >= CURRENT_TIMESTAMP
          )
    ) AS system_access
SQL,
            [
                $context->authIdentityId,
                $context->companyId,

                $context->authIdentityId,
                $context->businessUnitId,
                $context->companyId,

                $context->authIdentityId,
                $context->systemId,
            ]
        );

        return [
            'company' =>
                (bool) ($row->company_access ?? false),
            'business_unit' =>
                (bool) ($row->business_unit_access ?? false),
            'system' =>
                (bool) ($row->system_access ?? false),
        ];
    }

    /**
     * @return array{
     *     roles: list<string>,
     *     permissions: list<string>
     * }
     */
    private function authorizationRows(
        AccessContext $context
    ): array {
        $rows = collect(
            $this->database->select(
                <<<'SQL'
SELECT
    role_context.role_name,
    role_context.permission_name
FROM (
    SELECT
        role_catalog.name AS role_name,
        permission_catalog.name AS permission_name
    FROM access_role_contexts AS context_role
    INNER JOIN roles AS role_catalog
        ON role_catalog.id = context_role.role_id
       AND role_catalog.guard_name = 'web'
    LEFT JOIN role_has_permissions AS role_permission
        ON role_permission.role_id = context_role.role_id
    LEFT JOIN permissions AS permission_catalog
        ON permission_catalog.id =
            role_permission.permission_id
       AND permission_catalog.guard_name = 'web'
    WHERE context_role.auth_identity_id = ?
      AND context_role.status = 'active'
      AND (
          context_role.company_id IS NULL
          OR context_role.company_id = ?
      )
      AND (
          context_role.business_unit_id IS NULL
          OR context_role.business_unit_id = ?
      )
      AND (
          context_role.system_id IS NULL
          OR context_role.system_id = ?
      )
      AND (
          context_role.valid_from IS NULL
          OR context_role.valid_from <= CURRENT_TIMESTAMP
      )
      AND (
          context_role.valid_until IS NULL
          OR context_role.valid_until >= CURRENT_TIMESTAMP
      )
) AS role_context

UNION ALL

SELECT
    NULL AS role_name,
    direct_permission.name AS permission_name
FROM model_has_permissions AS direct_assignment
INNER JOIN permissions AS direct_permission
    ON direct_permission.id =
        direct_assignment.permission_id
   AND direct_permission.guard_name = 'web'
WHERE direct_assignment.model_type =
      'App\\Models\\AuthIdentity'
  AND direct_assignment.model_id = ?
SQL,
                [
                    $context->authIdentityId,
                    $context->companyId,
                    $context->businessUnitId,
                    $context->systemId,
                    $context->authIdentityId,
                ]
            )
        );

        return [
            'roles' => $this->uniqueStrings(
                $rows->pluck('role_name')
            ),
            'permissions' => $this->uniqueStrings(
                $rows->pluck('permission_name')
            ),
        ];
    }

    /**
     * @param Collection<int, mixed> $values
     * @return list<string>
     */
    private function uniqueStrings(
        Collection $values
    ): array {
        return $values
            ->filter(
                static fn ($value): bool =>
                    is_string($value)
                    && $value !== ''
            )
            ->map(
                static fn ($value): string =>
                    (string) $value
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, 'allow'|'deny'>
     */
    private function overrides(
        AccessContext $context
    ): array {
        return $this->database
            ->table(
                'access_component_overrides AS overrides'
            )
            ->leftJoin(
                'permissions AS permission_catalog',
                'permission_catalog.id',
                '=',
                'overrides.permission_id'
            )
            ->where(
                'overrides.auth_identity_id',
                $context->authIdentityId
            )
            ->where(
                'overrides.company_id',
                $context->companyId
            )
            ->where(
                'overrides.business_unit_id',
                $context->businessUnitId
            )
            ->where(
                'overrides.system_id',
                $context->systemId
            )
            ->where(
                'overrides.component_id',
                $context->componentId
            )
            ->where('overrides.status', 'active')
            ->where(function ($query): void {
                $query
                    ->whereNull('overrides.valid_from')
                    ->orWhere(
                        'overrides.valid_from',
                        '<=',
                        now()
                    );
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('overrides.valid_until')
                    ->orWhere(
                        'overrides.valid_until',
                        '>=',
                        now()
                    );
            })
            ->select([
                'permission_catalog.name AS permission_name',
                'overrides.effect',
            ])
            ->get()
            ->mapWithKeys(
                static function ($row): array {
                    $permission = $row->permission_name
                        ?: '*';

                    return [
                        (string) $permission =>
                            (string) $row->effect,
                    ];
                }
            )
            ->all();
    }
}
