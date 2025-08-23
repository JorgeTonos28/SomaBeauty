<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Washer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'pending_amount', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketWashes()
    {
        return $this->hasMany(TicketWash::class);
    }

    public function payments()
    {
        return $this->hasMany(WasherPayment::class);
    }

    public function movements()
    {
        return $this->hasMany(WasherMovement::class);
    }
}

