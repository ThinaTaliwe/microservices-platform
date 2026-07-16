<?php

namespace Tests\Unit\Authorization;

use PHPUnit\Framework\TestCase;

class ContextSchemaTest extends TestCase
{
    private string $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 3)
            . '/database/sql/iam-v2/schema/'
            . '002_create_context_access_tables.sql';

        $schema = file_get_contents($path);

        $this->assertNotFalse($schema);

        $this->schema = $schema;
    }

    public function test_nullable_role_context_is_normalized(): void
    {
        $this->assertStringContainsString(
            'company_scope_id BIGINT UNSIGNED',
            $this->schema
        );

        $this->assertStringContainsString(
            'business_unit_scope_id BIGINT UNSIGNED',
            $this->schema
        );

        $this->assertStringContainsString(
            'system_scope_id BIGINT UNSIGNED',
            $this->schema
        );

        $this->assertStringContainsString(
            'COALESCE(company_id, 0)',
            $this->schema
        );
    }

    public function test_nullable_permission_override_is_normalized(): void
    {
        $this->assertStringContainsString(
            'permission_scope_id BIGINT UNSIGNED',
            $this->schema
        );

        $this->assertStringContainsString(
            'COALESCE(permission_id, 0)',
            $this->schema
        );
    }

    public function test_context_tables_reference_auth_identity(): void
    {
        $this->assertStringContainsString(
            'REFERENCES auth_identities (id)',
            $this->schema
        );
    }

    public function test_explicit_deny_is_supported(): void
    {
        $this->assertStringContainsString(
            "effect ENUM('allow', 'deny')",
            $this->schema
        );
    }
}
