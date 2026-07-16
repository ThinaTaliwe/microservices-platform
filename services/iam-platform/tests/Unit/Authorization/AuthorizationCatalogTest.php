<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Catalog\CapabilityCatalog;
use App\Authorization\Catalog\PermissionCatalog;
use App\Authorization\Catalog\RoleCapabilityCatalog;
use App\Authorization\Catalog\RoleCatalog;
use PHPUnit\Framework\TestCase;

class AuthorizationCatalogTest extends TestCase
{
    public function test_role_names_are_unique(): void
    {
        $roles = RoleCatalog::all();

        $this->assertSame(
            count($roles),
            count(array_unique($roles))
        );
    }

    public function test_permission_names_are_unique_and_well_formed(): void
    {
        $permissions = PermissionCatalog::all();

        $this->assertSame(
            count($permissions),
            count(array_unique($permissions))
        );

        foreach ($permissions as $permission) {
            $this->assertMatchesRegularExpression(
                '/^[a-z0-9-]+\.[a-z0-9-]+\.[a-z0-9-]+$/',
                $permission
            );
        }
    }

    public function test_capabilities_only_reference_known_permissions(): void
    {
        $knownPermissions = PermissionCatalog::all();

        foreach (CapabilityCatalog::definitions() as $permissions) {
            foreach ($permissions as $permission) {
                $this->assertContains(
                    $permission,
                    $knownPermissions
                );
            }
        }
    }

    public function test_role_mappings_only_reference_known_roles(): void
    {
        $knownRoles = RoleCatalog::all();

        foreach (
            array_keys(RoleCapabilityCatalog::definitions())
            as $role
        ) {
            $this->assertContains($role, $knownRoles);
        }
    }

    public function test_client_user_has_no_global_iam_permissions(): void
    {
        $this->assertSame(
            [],
            RoleCapabilityCatalog::permissionsFor(
                RoleCatalog::CLIENT_USER
            )
        );
    }

    public function test_supervisor_receives_approval_permissions(): void
    {
        $permissions = RoleCapabilityCatalog::permissionsFor(
            RoleCatalog::SUPERVISOR
        );

        $this->assertContains(
            PermissionCatalog::APPROVALS_APPROVE,
            $permissions
        );

        $this->assertContains(
            PermissionCatalog::APPROVALS_BLOCK,
            $permissions
        );
    }
}
