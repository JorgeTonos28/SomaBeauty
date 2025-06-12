<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'type', 'service_id',
        'product_id', 'quantity', 'unit_price', 'subtotal'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
