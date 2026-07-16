<?php

namespace App\Authorization\Resolver;

use App\Authorization\Context\AccessContext;

final readonly class ContextResolution
{
    private function __construct(
        public bool $resolved,
        public ?AccessContext $context,
        public string $reason,
    ) {
    }

    public static function success(
        AccessContext $context
    ): self {
        return new self(
            resolved: true,
            context: $context,
            reason: 'resolved',
        );
    }

    public static function failure(
        string $reason
    ): self {
        return new self(
            resolved: false,
            context: null,
            reason: $reason,
        );
    }
}
