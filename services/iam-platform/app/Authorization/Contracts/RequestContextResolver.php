<?php

namespace App\Authorization\Contracts;

use App\Authorization\Resolver\ContextResolution;
use Illuminate\Http\Request;

interface RequestContextResolver
{
    public function resolve(
        Request $request,
        ?int $componentId = null
    ): ContextResolution;
}
