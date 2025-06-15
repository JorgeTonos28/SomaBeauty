<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'discountable_id',
        'discountable_type',
        'amount_type',
        'amount',
        'start_at',
        'end_at',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'end_at' => 'datetime',
        'start_at' => 'datetime',
    ];

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function logs()
    {
        return $this->hasMany(DiscountLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
