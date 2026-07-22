<?php

namespace App\Authorization\Contracts;

interface ContextRoleAssignmentCatalogue
{
    /**
     * @return array{
     *     identities: array<int, array<string, int|string|null>>,
     *     roles: array<int, array<string, int|string>>,
     *     companies: array<int, array<string, int|string>>,
     *     business_units: array<int, array<string, int|string>>,
     *     systems: array<int, array<string, int|string>>
     * }
     */
    public function get(): array;
}
