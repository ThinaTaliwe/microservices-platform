<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeOfTransport extends Model
{
    use HasFactory;

    
    protected $table = 'modes_of_transport';

    protected $fillable = [
        'name', // e.g., Air, Sea, Road, Rail
    ];

    /**
     * Relationship: One mode can be assigned to many shipments.
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'mode_id');
    }
}