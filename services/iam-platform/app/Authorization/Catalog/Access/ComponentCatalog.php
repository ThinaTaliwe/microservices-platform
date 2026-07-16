<?php

namespace App\Authorization\Catalog\Access;

use App\Authorization\Catalog\PermissionCatalog;

final class ComponentCatalog
{
    public const SESSIONS = 'sessions';
    public const DEVICES = 'devices';
    public const AUDIT = 'audit';
    public const APPROVALS = 'approvals';
    public const IDENTITIES = 'identities';
    public const ROLES = 'roles';
    public const PERMISSIONS = 'permissions';
    public const CONTEXTS = 'contexts';

    /**
     * @return array<string, array{
     *     module: string,
     *     name: string,
     *     external_key: string,
     *     display_order: int,
     *     permissions: list<string>
     * }>
     */
    public static function definitions(): array
    {
        return [
            self::SESSIONS => [
                'module' => ModuleCatalog::SECURITY,
                'name' => 'Sessions',
                'external_key' => 'iam-sessions',
                'display_order' => 10,
                'permissions' => [
                    PermissionCatalog::SESSIONS_READ,
                    PermissionCatalog::SESSIONS_REVOKE,
                ],
            ],

            self::DEVICES => [
                'module' => ModuleCatalog::SECURITY,
                'name' => 'Trusted Devices',
                'external_key' => 'iam-devices',
                'display_order' => 20,
                'permissions' => [
                    PermissionCatalog::DEVICES_READ,
                    PermissionCatalog::DEVICES_REMOVE_TRUST,
                ],
            ],

            self::AUDIT => [
                'module' => ModuleCatalog::SECURITY,
                'name' => 'Security Audit',
                'external_key' => 'iam-audit',
                'display_order' => 30,
                'permissions' => [
                    PermissionCatalog::AUDIT_READ,
                ],
            ],

            self::APPROVALS => [
                'module' => ModuleCatalog::ACCESS_MANAGEMENT,
                'name' => 'Supervisor Approvals',
                'external_key' => 'iam-approvals',
                'display_order' => 10,
                'permissions' => [
                    PermissionCatalog::APPROVALS_READ,
                    PermissionCatalog::APPROVALS_APPROVE,
                    PermissionCatalog::APPROVALS_BLOCK,
                ],
            ],

            self::IDENTITIES => [
                'module' => ModuleCatalog::ACCESS_MANAGEMENT,
                'name' => 'Identities',
                'external_key' => 'iam-identities',
                'display_order' => 20,
                'permissions' => [
                    PermissionCatalog::IDENTITIES_READ,
                    PermissionCatalog::IDENTITIES_MANAGE,
                ],
            ],

            self::ROLES => [
                'module' => ModuleCatalog::ACCESS_MANAGEMENT,
                'name' => 'Roles',
                'external_key' => 'iam-roles',
                'display_order' => 30,
                'permissions' => [
                    PermissionCatalog::ROLES_READ,
                    PermissionCatalog::ROLES_MANAGE,
                ],
            ],

            self::PERMISSIONS => [
                'module' => ModuleCatalog::ACCESS_MANAGEMENT,
                'name' => 'Permissions',
                'external_key' => 'iam-permissions',
                'display_order' => 40,
                'permissions' => [
                    PermissionCatalog::PERMISSIONS_READ,
                    PermissionCatalog::PERMISSIONS_MANAGE,
                ],
            ],

            self::CONTEXTS => [
                'module' => ModuleCatalog::ACCESS_MANAGEMENT,
                'name' => 'Access Contexts',
                'external_key' => 'iam-contexts',
                'display_order' => 50,
                'permissions' => [
                    PermissionCatalog::CONTEXT_READ,
                    PermissionCatalog::CONTEXT_MANAGE,
                ],
            ],
        ];
    }

    private function __construct()
    {
    }
}
