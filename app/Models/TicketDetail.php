<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Drink;

class TicketDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'ticket_wash_id', 'type', 'service_id',
        'product_id', 'drink_id', 'quantity', 'unit_price', 'discount_amount', 'subtotal', 'description'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function wash()
    {
        return $this->belongsTo(TicketWash::class, 'ticket_wash_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function drink()
    {
        return $this->belongsTo(Drink::class);
    }
}
