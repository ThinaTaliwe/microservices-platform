<?php

namespace App\Authorization\Catalog;

final class CapabilityCatalog
{
    public const SESSION_SECURITY = 'iam.session-security';
    public const ACCESS_REVIEWS = 'iam.access-reviews';
    public const IDENTITY_ADMINISTRATION = 'iam.identity-administration';
    public const AUTHORIZATION_ADMINISTRATION =
        'iam.authorization-administration';
    public const SECURITY_AUDIT = 'iam.security-audit';

    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            self::SESSION_SECURITY => [
                PermissionCatalog::SESSIONS_READ,
                PermissionCatalog::SESSIONS_REVOKE,
                PermissionCatalog::DEVICES_READ,
                PermissionCatalog::DEVICES_REMOVE_TRUST,
            ],

            self::ACCESS_REVIEWS => [
                PermissionCatalog::APPROVALS_READ,
                PermissionCatalog::APPROVALS_APPROVE,
                PermissionCatalog::APPROVALS_BLOCK,
            ],

            self::IDENTITY_ADMINISTRATION => [
                PermissionCatalog::IDENTITIES_READ,
                PermissionCatalog::IDENTITIES_MANAGE,
                PermissionCatalog::CONTEXT_READ,
                PermissionCatalog::CONTEXT_MANAGE,
            ],

            self::AUTHORIZATION_ADMINISTRATION => [
                PermissionCatalog::ROLES_READ,
                PermissionCatalog::ROLES_MANAGE,
                PermissionCatalog::PERMISSIONS_READ,
                PermissionCatalog::PERMISSIONS_MANAGE,
                PermissionCatalog::CONTEXT_READ,
                PermissionCatalog::CONTEXT_MANAGE,
            ],

            self::SECURITY_AUDIT => [
                PermissionCatalog::SESSIONS_READ,
                PermissionCatalog::DEVICES_READ,
                PermissionCatalog::AUDIT_READ,
                PermissionCatalog::APPROVALS_READ,
                PermissionCatalog::IDENTITIES_READ,
                PermissionCatalog::ROLES_READ,
                PermissionCatalog::PERMISSIONS_READ,
                PermissionCatalog::CONTEXT_READ,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissions(string $capability): array
    {
        return self::definitions()[$capability] ?? [];
    }

    private function __construct()
    {
    }
}
