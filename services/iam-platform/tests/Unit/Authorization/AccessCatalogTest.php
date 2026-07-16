<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Catalog\Access\ComponentCatalog;
use App\Authorization\Catalog\Access\ModuleCatalog;
use App\Authorization\Catalog\Access\SystemCatalog;
use App\Authorization\Catalog\PermissionCatalog;
use PHPUnit\Framework\TestCase;

class AccessCatalogTest extends TestCase
{
    public function test_modules_reference_known_systems(): void
    {
        $systems = array_keys(
            SystemCatalog::definitions()
        );

        foreach (ModuleCatalog::definitions() as $module) {
            $this->assertContains(
                $module['system'],
                $systems
            );
        }
    }

    public function test_components_reference_known_modules(): void
    {
        $modules = array_keys(
            ModuleCatalog::definitions()
        );

        foreach (
            ComponentCatalog::definitions()
            as $component
        ) {
            $this->assertContains(
                $component['module'],
                $modules
            );
        }
    }

    public function test_components_only_reference_known_permissions(): void
    {
        $permissions = PermissionCatalog::all();

        foreach (
            ComponentCatalog::definitions()
            as $component
        ) {
            foreach ($component['permissions'] as $permission) {
                $this->assertContains(
                    $permission,
                    $permissions
                );
            }
        }
    }

    public function test_catalogue_identifiers_are_unique(): void
    {
        $components = ComponentCatalog::definitions();

        $externalKeys = array_column(
            $components,
            'external_key'
        );

        $this->assertSame(
            count($externalKeys),
            count(array_unique($externalKeys))
        );
    }

    public function test_every_iam_permission_has_a_component(): void
    {
        $mappedPermissions = [];

        foreach (
            ComponentCatalog::definitions()
            as $component
        ) {
            $mappedPermissions = [
                ...$mappedPermissions,
                ...$component['permissions'],
            ];
        }

        sort($mappedPermissions);

        $expectedPermissions = PermissionCatalog::all();
        sort($expectedPermissions);

        $this->assertSame(
            $expectedPermissions,
            array_values(
                array_unique($mappedPermissions)
            )
        );
    }
}
