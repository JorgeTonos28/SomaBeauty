<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active'];

    public function prices()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function details()
    {
        return $this->hasMany(TicketDetail::class);
    }
}

