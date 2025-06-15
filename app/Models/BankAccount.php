<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ticket;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank',
        'account',
        'type',
        'holder',
        'holder_cedula',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
