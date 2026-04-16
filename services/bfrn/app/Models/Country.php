<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_name',
        'iso_code',
    ];

    /**
     * Relationship: A country can be the origin for many shipments.
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'origin_country_id');
    }
}