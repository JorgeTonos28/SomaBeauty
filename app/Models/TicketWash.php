<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketWash extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'vehicle_id',
        'vehicle_type_id',
        'washer_id',
        'washer_paid',
        'tip',
    ];

    protected $casts = [
        'washer_paid' => 'boolean',
        'tip' => 'float',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function washer()
    {
        return $this->belongsTo(Washer::class);
    }

    public function details()
    {
        return $this->hasMany(TicketDetail::class, 'ticket_wash_id');
    }
}
