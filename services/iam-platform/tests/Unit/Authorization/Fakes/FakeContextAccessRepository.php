<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\ContextAccessRepository;
use App\Authorization\Context\AccessContext;
use App\Authorization\Snapshot\AccessSnapshot;

class FakeContextAccessRepository implements ContextAccessRepository
{
    public int $calls = 0;

    public function __construct(
        private AccessSnapshot $snapshot
    ) {
    }

    public function snapshot(
        AccessContext $context
    ): AccessSnapshot {
        $this->calls++;

        return $this->snapshot;
    }

    public function replace(
        AccessSnapshot $snapshot
    ): void {
        $this->snapshot = $snapshot;
    }
}
