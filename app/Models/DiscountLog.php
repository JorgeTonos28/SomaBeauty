<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'discount_id',
        'user_id',
        'action',
        'amount_type',
        'amount',
        'end_at',
    ];

    protected $casts = [
        'end_at' => 'datetime',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
