<?php

namespace App\Authorization\Contracts;

use App\Authorization\Context\AccessContext;
use App\Authorization\Snapshot\AccessSnapshot;

interface ContextAccessRepository
{
    public function snapshot(
        AccessContext $context
    ): AccessSnapshot;
}
