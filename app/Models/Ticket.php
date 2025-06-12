<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'washer_id', 'vehicle_type_id',
        'total_amount', 'paid_amount', 'change', 'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function washer()
    {
        return $this->belongsTo(Washer::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function details()
    {
        return $this->hasMany(TicketDetail::class);
    }
}
