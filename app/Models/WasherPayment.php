<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WasherPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'washer_id', 'payment_date', 'total_washes', 'amount_paid', 'observations'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function washer()
    {
        return $this->belongsTo(Washer::class);
    }
}
