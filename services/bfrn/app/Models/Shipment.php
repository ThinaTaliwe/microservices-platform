<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    // Fields matching the inputs in ShipmentCreation.blade.php
    protected $fillable = [
        'client_id',
        'mode_id',
        'origin_country_id',
        'destination_address',
        'reference_no',
        'weight',
        'dimensions'
    ];
}