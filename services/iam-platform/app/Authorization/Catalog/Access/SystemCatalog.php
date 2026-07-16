<?php

namespace App\Authorization\Catalog\Access;

final class SystemCatalog
{
    public const IAM = 'iam';

    /**
     * @return array<string, array{
     *     name: string,
     *     external_key: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            self::IAM => [
                'name' => 'IAM Platform',
                'external_key' => 'iam-platform',
            ],
        ];
    }

    private function __construct()
    {
    }
}
