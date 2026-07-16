<?php

namespace Tests\Unit\Authorization;

use App\Authorization\Snapshot\AccessSnapshot;
use PHPUnit\Framework\TestCase;

class AccessSnapshotTest extends TestCase
{
    public function test_snapshot_survives_cache_serialization(): void
    {
        $snapshot = new AccessSnapshot(
            companyAccess: true,
            businessUnitAccess: true,
            systemAccess: true,
            roles: ['supervisor'],
            permissions: ['iam.sessions.read'],
            overrides: [
                'iam.sessions.revoke' => 'deny',
            ],
        );

        $restored = AccessSnapshot::fromArray(
            $snapshot->toArray()
        );

        $this->assertSame(
            $snapshot->toArray(),
            $restored->toArray()
        );
    }
}
