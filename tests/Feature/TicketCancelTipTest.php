<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\Ticket;
use App\Models\TicketWash;
use App\Models\WasherMovement;

class TicketCancelTipTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_removes_tip_from_washer()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create(['name' => 'W', 'pending_amount' => 0, 'active' => true]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'efectivo',
            'washer_pending_amount' => 0,
            'customer_name' => 'Cliente',
            'pending' => false,
            'canceled' => false,
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => $washer->id,
            'vehicle_id' => null,
            'vehicle_type_id' => null,
            'washer_paid' => false,
            'tip' => 30,
        ]);
        \App\Models\TicketDetail::create([
            'ticket_id' => $ticket->id,
            'ticket_wash_id' => $wash->id,
            'type' => 'service',
            'service_id' => null,
            'product_id' => null,
            'drink_id' => null,
            'quantity' => 1,
            'unit_price' => 0,
            'discount_amount' => 0,
            'subtotal' => 0,
        ]);

        WasherMovement::create([
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 30,
            'description' => '[P] Test',
        ]);
        $washer->increment('pending_amount', 130);

        $this->actingAs($user)->post(route('tickets.cancel', $ticket), [
            'cancel_reason' => 'test',
            'pay_washer' => 'no',
        ]);

        $washer->refresh();
        $this->assertEquals(0, $washer->pending_amount);
        $this->assertDatabaseMissing('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 30,
        ]);
    }
}
