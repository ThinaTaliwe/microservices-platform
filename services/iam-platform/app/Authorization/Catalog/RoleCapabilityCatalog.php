<?php

namespace App\Authorization\Catalog;

final class RoleCapabilityCatalog
{
    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            RoleCatalog::SUPERVISOR => [
                CapabilityCatalog::ACCESS_REVIEWS,
                CapabilityCatalog::SECURITY_AUDIT,
            ],

            RoleCatalog::AUDITOR => [
                CapabilityCatalog::SECURITY_AUDIT,
            ],

            RoleCatalog::READ_ONLY => [
                CapabilityCatalog::SECURITY_AUDIT,
            ],

            RoleCatalog::CLIENT_USER => [],
            RoleCatalog::OPERATIONS_USER => [],
            RoleCatalog::OPERATIONS_MANAGER => [],
            RoleCatalog::COMPANY_ADMIN => [],
            RoleCatalog::BU_ADMIN => [],
            RoleCatalog::SYSTEM_ADMIN => [],
            RoleCatalog::API_USER => [],
            RoleCatalog::SERVICE_ACCOUNT => [],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissionsFor(string $role): array
    {
        $permissions = [];

        foreach (self::definitions()[$role] ?? [] as $capability) {
            $permissions = [
                ...$permissions,
                ...CapabilityCatalog::permissions($capability),
            ];
        }

        return array_values(array_unique($permissions));
    }

    private function __construct()
    {
    }
}
