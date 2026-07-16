<?php

namespace App\Authorization\Context;

final class AccessStatus
{
    public const ACTIVE = 'active';
    public const SUSPENDED = 'suspended';
    public const EXPIRED = 'expired';
    public const REVOKED = 'revoked';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::SUSPENDED,
            self::EXPIRED,
            self::REVOKED,
        ];
    }

    private function __construct()
    {
    }
}
