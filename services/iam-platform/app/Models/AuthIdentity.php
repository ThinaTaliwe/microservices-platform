<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Hidden([
    'email_hash',
    'email_encrypted',
])]
class AuthIdentity extends Authenticatable
{
    use HasRoles;
    use Notifiable;

    protected $table = 'auth_identities';

    protected $guarded = [];

    protected string $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'bfrn_user_id' => 'integer',
            'last_seen_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * IAM identities authenticate through OTP and IAM sessions.
     * They do not use Laravel password authentication.
     */
    public function getAuthPassword(): string
    {
        return '';
    }
}
