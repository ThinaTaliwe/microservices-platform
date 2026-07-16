<?php

namespace App\Authorization\Contracts;

interface ComponentReferenceResolver
{
    public function resolve(
        string $reference
    ): ?int;

    public function forget(
        ?string $reference = null
    ): void;
}
