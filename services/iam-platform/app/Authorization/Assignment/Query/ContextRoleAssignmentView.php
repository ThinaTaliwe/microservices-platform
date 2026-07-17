<?php

namespace App\Authorization\Assignment\Query;

final readonly class ContextRoleAssignmentView
{
    public function __construct(
        public int $id,
        public int $authIdentityId,
        public int $roleId,
        public string $roleName,
        public ?int $companyId,
        public ?string $companyName,
        public ?int $businessUnitId,
        public ?string $businessUnitName,
        public ?int $systemId,
        public ?string $systemName,
        public string $status,
        public ?string $validFrom,
        public ?string $validUntil,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    public static function fromRow(object $row): self
    {
        return new self(
            id: (int) $row->id,
            authIdentityId:
                (int) $row->auth_identity_id,
            roleId: (int) $row->role_id,
            roleName: (string) $row->role_name,
            companyId: $row->company_id === null
                ? null
                : (int) $row->company_id,
            companyName: $row->company_name === null
                ? null
                : (string) $row->company_name,
            businessUnitId:
                $row->business_unit_id === null
                    ? null
                    : (int) $row->business_unit_id,
            businessUnitName:
                $row->business_unit_name === null
                    ? null
                    : (string) $row->business_unit_name,
            systemId: $row->system_id === null
                ? null
                : (int) $row->system_id,
            systemName: $row->system_name === null
                ? null
                : (string) $row->system_name,
            status: (string) $row->status,
            validFrom: $row->valid_from === null
                ? null
                : (string) $row->valid_from,
            validUntil: $row->valid_until === null
                ? null
                : (string) $row->valid_until,
            createdAt: $row->created_at === null
                ? null
                : (string) $row->created_at,
            updatedAt: $row->updated_at === null
                ? null
                : (string) $row->updated_at,
        );
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'auth_identity_id' =>
                $this->authIdentityId,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'company_id' => $this->companyId,
            'company_name' => $this->companyName,
            'business_unit_id' =>
                $this->businessUnitId,
            'business_unit_name' =>
                $this->businessUnitName,
            'system_id' => $this->systemId,
            'system_name' => $this->systemName,
            'status' => $this->status,
            'valid_from' => $this->validFrom,
            'valid_until' => $this->validUntil,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
