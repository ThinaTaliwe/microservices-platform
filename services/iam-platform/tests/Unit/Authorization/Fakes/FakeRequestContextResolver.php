<?php

namespace Tests\Unit\Authorization\Fakes;

use App\Authorization\Contracts\RequestContextResolver;
use App\Authorization\Resolver\ContextResolution;
use Illuminate\Http\Request;

class FakeRequestContextResolver implements
    RequestContextResolver
{
    public ?int $receivedComponentId = null;

    public function __construct(
        private ContextResolution $resolution
    ) {
    }

    public function resolve(
        Request $request,
        ?int $componentId = null
    ): ContextResolution {
        $this->receivedComponentId = $componentId;

        return $this->resolution;
    }
}
