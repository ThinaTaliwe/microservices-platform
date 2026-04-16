<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Based on ERD, these are typical fields for a client record.
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'company_name',
    ];

    /**
     * Relationship: A Client can have many Shipments.
     * This links back to the 'client_id' foreign key in shipments table.
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}