<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'vehicle_type_id',
        'plate',
        'brand',
        'model',
        'color',
        'year',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
