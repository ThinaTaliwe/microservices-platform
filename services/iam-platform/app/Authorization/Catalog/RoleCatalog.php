<?php

namespace App\Authorization\Catalog;

final class RoleCatalog
{
    public const PLATFORM_SUPER_ADMIN = 'platform-super-admin';
    public const SYSTEM_OWNER = 'system-owner';
    public const COMPANY_ADMIN = 'company-admin';
    public const BU_ADMIN = 'bu-admin';
    public const SYSTEM_ADMIN = 'system-admin';
    public const SUPERVISOR = 'supervisor';
    public const OPERATIONS_MANAGER = 'operations-manager';
    public const OPERATIONS_USER = 'operations-user';
    public const CLIENT_USER = 'client-user';
    public const AUDITOR = 'auditor';
    public const READ_ONLY = 'read-only';
    public const API_USER = 'api-user';
    public const SERVICE_ACCOUNT = 'service-account';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PLATFORM_SUPER_ADMIN,
            self::SYSTEM_OWNER,
            self::COMPANY_ADMIN,
            self::BU_ADMIN,
            self::SYSTEM_ADMIN,
            self::SUPERVISOR,
            self::OPERATIONS_MANAGER,
            self::OPERATIONS_USER,
            self::CLIENT_USER,
            self::AUDITOR,
            self::READ_ONLY,
            self::API_USER,
            self::SERVICE_ACCOUNT,
        ];
    }

    private function __construct()
    {
    }
}
