<?php

namespace App\Authorization\Catalog\Access;

final class ModuleCatalog
{
    public const SECURITY = 'security';
    public const ACCESS_MANAGEMENT = 'access-management';

    /**
     * @return array<string, array{
     *     system: string,
     *     name: string,
     *     external_key: string,
     *     display_order: int
     * }>
     */
    public static function definitions(): array
    {
        return [
            self::SECURITY => [
                'system' => SystemCatalog::IAM,
                'name' => 'Security',
                'external_key' => 'iam-security',
                'display_order' => 10,
            ],

            self::ACCESS_MANAGEMENT => [
                'system' => SystemCatalog::IAM,
                'name' => 'Access Management',
                'external_key' => 'iam-access-management',
                'display_order' => 20,
            ],
        ];
    }

    private function __construct()
    {
    }
}
