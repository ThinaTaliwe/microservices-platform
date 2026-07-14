<?php

namespace App\Services\Bfrn;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BfrnProvisioningService
{
    public function findUserByEmail(string $email): ?object
    {
        return DB::table('users')
            ->where('email', strtolower(trim($email)))
            ->first(['id', 'name', 'email', 'welcome_valid_until']);
    }

    public function listBusinessUnits()
    {
        return DB::table('bu')
            ->select('id', 'bu_name', 'short_code', 'system_id')
            ->orderBy('bu_name')
            ->get();
    }

    public function provisionUser(string $email, ?int $businessUnitId = null): array
    {
        $email = strtolower(trim($email));
        $temporaryPassword = Str::password(14);
        $businessUnitId = $businessUnitId ?: (int) env('BFRN_DEFAULT_BU_ID', 8);

        return DB::transaction(function () use ($email, $temporaryPassword, $businessUnitId) {
            $user = $this->findUserByEmail($email);

            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $email,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make($temporaryPassword),
                    'must_change_password' => 1,
                    'welcome_valid_until' => now()->addYear(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $userId = (int) $user->id;

                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'email_verified_at' => now(),
                        'password' => Hash::make($temporaryPassword),
                        'must_change_password' => 1,
                        'welcome_valid_until' => now()->addYear(),
                        'updated_at' => now(),
                    ]);
            }

            $this->assignBusinessUnit($userId, $businessUnitId);

            return [
                'user_id' => $userId,
                'email' => $email,
                'temporary_password' => $temporaryPassword,
                'created' => !$user,
            ];
        });
    }

    public function assignBusinessUnit(int $userId, int $businessUnitId): bool
    {
        $bu = DB::table('bu')
            ->where('id', $businessUnitId)
            ->first(['id', 'system_id']);

        if (!$bu) {
            return false;
        }

        DB::table('users_has_bu')->updateOrInsert(
            [
                'users_id' => $userId,
                'bu_id' => $bu->id,
            ],
            [
                'requested' => 0,
                'has_access' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('user_has_system')->updateOrInsert(
            [
                'users_id' => $userId,
                'system_id' => $bu->system_id,
                'bu_id' => $bu->id,
            ],
            [
                'full_access' => 1,
                'system_size' => 'S',
                'company_id' => null,
                'component_id' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return true;
    }
}
