<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\Session\SessionValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionValidationService $sessions
    ) {
    }

    public function validateSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'integer', 'min:1'],
            'bfrn_user_id' => ['required', 'integer', 'min:1'],
        ]);

        $session = $this->sessions->validate(
            (int) $data['session_id'],
            (int) $data['bfrn_user_id']
        );

        if (!$session) {
            return response()->json([
                'valid' => false,
                'message' => 'IAM session is invalid, expired, or revoked.',
            ], 403);
        }

        return response()->json([
            'valid' => true,
            'session' => $session,
        ]);
    }
}
