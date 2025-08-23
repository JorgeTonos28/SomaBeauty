<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class TicketExtraChargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_with_extra_charge_is_persisted(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $vehicleType = VehicleType::create(['name' => 'Carro']);
        $date = Carbon::today();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_id' => null,
            'customer_name' => 'Cliente',
            'customer_cedula' => null,
            'customer_phone' => null,
            'total_amount' => 50,
            'paid_amount' => 50,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'canceled' => false,
            'cancel_reason' => null,
            'pending' => false,
            'paid_at' => $date,
        ]);

        TicketDetail::create([
            'ticket_id' => $ticket->id,
            'type' => 'extra',
            'service_id' => null,
            'product_id' => null,
            'drink_id' => null,
            'quantity' => 1,
            'unit_price' => 50,
            'discount_amount' => 0,
            'subtotal' => 50,
            'description' => 'Cargo adicional',
        ]);

        $this->assertDatabaseHas('ticket_details', [
            'ticket_id' => $ticket->id,
            'type' => 'extra',
            'description' => 'Cargo adicional',
        ]);
    }
}

