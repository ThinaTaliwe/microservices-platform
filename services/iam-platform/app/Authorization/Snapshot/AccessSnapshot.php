<?php

namespace App\Authorization\Snapshot;

final readonly class AccessSnapshot
{
    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     * @param array<string, 'allow'|'deny'> $overrides
     */
    public function __construct(
        public bool $companyAccess,
        public bool $businessUnitAccess,
        public bool $systemAccess,
        public array $roles,
        public array $permissions,
        public array $overrides,
    ) {
    }

    public static function denied(): self
    {
        return new self(
            companyAccess: false,
            businessUnitAccess: false,
            systemAccess: false,
            roles: [],
            permissions: [],
            overrides: [],
        );
    }

    /**
     * @param array{
     *     company_access: bool,
     *     business_unit_access: bool,
     *     system_access: bool,
     *     roles: list<string>,
     *     permissions: list<string>,
     *     overrides: array<string, 'allow'|'deny'>
     * } $payload
     */
    public static function fromArray(
        array $payload
    ): self {
        return new self(
            companyAccess:
                (bool) $payload['company_access'],
            businessUnitAccess:
                (bool) $payload['business_unit_access'],
            systemAccess:
                (bool) $payload['system_access'],
            roles: array_values(
                array_unique($payload['roles'])
            ),
            permissions: array_values(
                array_unique($payload['permissions'])
            ),
            overrides: $payload['overrides'],
        );
    }

    /**
     * @return array{
     *     company_access: bool,
     *     business_unit_access: bool,
     *     system_access: bool,
     *     roles: list<string>,
     *     permissions: list<string>,
     *     overrides: array<string, 'allow'|'deny'>
     * }
     */
    public function toArray(): array
    {
        return [
            'company_access' => $this->companyAccess,
            'business_unit_access' =>
                $this->businessUnitAccess,
            'system_access' => $this->systemAccess,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'overrides' => $this->overrides,
        ];
    }

    public function membershipsAreValid(): bool
    {
        return $this->companyAccess
            && $this->businessUnitAccess
            && $this->systemAccess;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasPermission(
        string $permission
    ): bool {
        return in_array(
            $permission,
            $this->permissions,
            true
        );
    }

    public function overrideFor(
        string $permission
    ): ?string {
        return $this->overrides[$permission]
            ?? $this->overrides['*']
            ?? null;
    }
}
