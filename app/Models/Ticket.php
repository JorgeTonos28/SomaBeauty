<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'washer_id', 'vehicle_type_id',
        'customer_name', 'customer_cedula',
        'total_amount', 'paid_amount', 'change', 'discount_total', 'payment_method', 'bank_account_id', 'canceled'
    ];

    protected $casts = [
        'canceled' => 'boolean',
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

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
