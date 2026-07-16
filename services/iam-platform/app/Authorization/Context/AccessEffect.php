<?php

namespace App\Authorization\Context;

final class AccessEffect
{
    public const ALLOW = 'allow';
    public const DENY = 'deny';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ALLOW,
            self::DENY,
        ];
    }

    private function __construct()
    {
    }
}
