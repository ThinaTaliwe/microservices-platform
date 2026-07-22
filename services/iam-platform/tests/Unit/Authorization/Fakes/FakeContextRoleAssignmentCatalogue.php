<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\ContextRoleAssignmentCatalogue;

class FakeContextRoleAssignmentCatalogue implements
    ContextRoleAssignmentCatalogue
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data
    ) {
    }

    public function get(): array
    {
        return $this->data;
    }
}
