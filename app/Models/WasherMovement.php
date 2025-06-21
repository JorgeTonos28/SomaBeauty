<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WasherMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'washer_id', 'ticket_id', 'amount', 'description'
    ];

    public function washer()
    {
        return $this->belongsTo(Washer::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
