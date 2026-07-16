<?php

namespace App\Authorization\Catalog;

final class PermissionCatalog
{
    public const SESSIONS_READ = 'iam.sessions.read';
    public const SESSIONS_REVOKE = 'iam.sessions.revoke';

    public const DEVICES_READ = 'iam.devices.read';
    public const DEVICES_REMOVE_TRUST = 'iam.devices.remove-trust';

    public const AUDIT_READ = 'iam.audit.read';

    public const APPROVALS_READ = 'iam.approvals.read';
    public const APPROVALS_APPROVE = 'iam.approvals.approve';
    public const APPROVALS_BLOCK = 'iam.approvals.block';

    public const IDENTITIES_READ = 'iam.identities.read';
    public const IDENTITIES_MANAGE = 'iam.identities.manage';

    public const ROLES_READ = 'iam.roles.read';
    public const ROLES_MANAGE = 'iam.roles.manage';

    public const PERMISSIONS_READ = 'iam.permissions.read';
    public const PERMISSIONS_MANAGE = 'iam.permissions.manage';

    public const CONTEXT_READ = 'iam.context.read';
    public const CONTEXT_MANAGE = 'iam.context.manage';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SESSIONS_READ,
            self::SESSIONS_REVOKE,
            self::DEVICES_READ,
            self::DEVICES_REMOVE_TRUST,
            self::AUDIT_READ,
            self::APPROVALS_READ,
            self::APPROVALS_APPROVE,
            self::APPROVALS_BLOCK,
            self::IDENTITIES_READ,
            self::IDENTITIES_MANAGE,
            self::ROLES_READ,
            self::ROLES_MANAGE,
            self::PERMISSIONS_READ,
            self::PERMISSIONS_MANAGE,
            self::CONTEXT_READ,
            self::CONTEXT_MANAGE,
        ];
    }

    private function __construct()
    {
    }
}
