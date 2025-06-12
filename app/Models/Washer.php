<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Washer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments()
    {
        return $this->hasMany(WasherPayment::class);
    }
}

