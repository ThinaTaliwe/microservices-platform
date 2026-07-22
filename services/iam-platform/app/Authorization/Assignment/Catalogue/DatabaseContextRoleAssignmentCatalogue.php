<?php

namespace App\Authorization\Assignment\Catalogue;

use App\Authorization\Contracts\ContextRoleAssignmentCatalogue;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\ConnectionInterface;

class DatabaseContextRoleAssignmentCatalogue implements
    ContextRoleAssignmentCatalogue
{
    public function __construct(
        private readonly ConnectionInterface $database,
        private readonly Encrypter $encrypter,
    ) {
    }

    public function get(): array
    {
        return [
            'identities' => $this->identities(),
            'roles' => $this->roles(),
            'companies' => $this->companies(),
            'business_units' => $this->businessUnits(),
            'systems' => $this->systems(),
        ];
    }

    /**
     * @return array<int, array<string, int|string|null>>
     */
    private function identities(): array
    {
        return $this->database
            ->table('auth_identities')
            ->where('status', 'active')
            ->orderBy('id')
            ->get([
                'id',
                'bfrn_user_id',
                'email_encrypted',
            ])
            ->map(function (object $identity): array {
                $email = $this->decryptEmail(
                    (string) $identity->email_encrypted
                );

                return [
                    'id' => (int) $identity->id,
                    'bfrn_user_id' =>
                        $identity->bfrn_user_id === null
                            ? null
                            : (int) $identity->bfrn_user_id,
                    'label' =>
                        $this->maskedEmail($email)
                        . ' · ID '
                        . (int) $identity->id,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function roles(): array
    {
        return $this->database
            ->table('roles')
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ])
            ->map(
                static fn (object $role): array => [
                    'id' => (int) $role->id,
                    'name' => (string) $role->name,
                    'label' => ucwords(
                        str_replace(
                            '-',
                            ' ',
                            (string) $role->name
                        )
                    ),
                ]
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function companies(): array
    {
        return $this->database
            ->table('access_companies')
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ])
            ->map(
                static fn (object $company): array => [
                    'id' => (int) $company->id,
                    'name' => (string) $company->name,
                ]
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function businessUnits(): array
    {
        return $this->database
            ->table('access_business_units')
            ->where('status', 'active')
            ->orderBy('company_id')
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'name',
            ])
            ->map(
                static fn (object $businessUnit): array => [
                    'id' => (int) $businessUnit->id,
                    'company_id' =>
                        (int) $businessUnit->company_id,
                    'name' => (string) $businessUnit->name,
                ]
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function systems(): array
    {
        return $this->database
            ->table('access_systems')
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ])
            ->map(
                static fn (object $system): array => [
                    'id' => (int) $system->id,
                    'name' => (string) $system->name,
                    'slug' => (string) $system->slug,
                ]
            )
            ->values()
            ->all();
    }

    private function decryptEmail(
        string $encryptedEmail
    ): string {
        try {
            return $this->encrypter->decryptString(
                $encryptedEmail
            );
        } catch (DecryptException) {
            return '';
        }
    }

    private function maskedEmail(
        string $email
    ): string {
        if (
            $email === ''
            || !str_contains($email, '@')
        ) {
            return 'Unavailable email';
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($local === '' || $domain === '') {
            return 'Unavailable email';
        }

        $visibleLength = min(
            2,
            mb_strlen($local)
        );

        return mb_substr(
            $local,
            0,
            $visibleLength
        )
            . '***@'
            . $domain;
    }
}
