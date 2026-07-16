<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\ComponentReferenceResolver;

class FakeComponentReferenceResolver implements
    ComponentReferenceResolver
{
    public ?string $receivedReference = null;

    public function __construct(
        private readonly ?int $componentId
    ) {
    }

    public function resolve(
        string $reference
    ): ?int {
        $this->receivedReference = $reference;

        return $this->componentId;
    }

    public function forget(
        ?string $reference = null
    ): void {
        //
    }
}
