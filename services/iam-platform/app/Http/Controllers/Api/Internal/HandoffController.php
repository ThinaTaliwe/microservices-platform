<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\Handoff\HandoffTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HandoffController extends Controller
{
    public function __construct(
        private readonly HandoffTokenService $handoffTokens
    ) {
    }

    public function consume(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $handoff = $this->handoffTokens->consume($data['token']);

        if (!$handoff) {
            return response()->json([
                'message' => 'Invalid, expired, or already-used handoff token.',
            ], 403);
        }

        return response()->json([
            'consumed' => true,
            'handoff' => $handoff,
        ]);
    }
}
