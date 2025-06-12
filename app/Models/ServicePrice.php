<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicePrice extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'vehicle_type_id', 'price'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}

