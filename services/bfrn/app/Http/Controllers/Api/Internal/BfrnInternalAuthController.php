<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Services\Bfrn\BfrnProvisioningService;
use Illuminate\Http\Request;

class BfrnInternalAuthController extends Controller
{
    public function __construct(
        private readonly BfrnProvisioningService $provisioning
    ) {}

    public function findUser(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $this->provisioning->findUserByEmail($data['email']);

        return response()->json([
            'found' => (bool) $user,
            'user' => $user,
        ]);
    }

    public function businessUnits()
    {
        return response()->json([
            'business_units' => $this->provisioning->listBusinessUnits(),
        ]);
    }

    public function provisionUser(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'business_unit_id' => ['nullable', 'integer'],
        ]);

        return response()->json(
            $this->provisioning->provisionUser(
                $data['email'],
                $data['business_unit_id'] ?? null
            )
        );
    }

    public function assignBusinessUnit(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'business_unit_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'assigned' => $this->provisioning->assignBusinessUnit(
                (int) $data['user_id'],
                (int) $data['business_unit_id']
            ),
        ]);
    }
}
