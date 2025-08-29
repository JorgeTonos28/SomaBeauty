<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\Ticket;
use App\Models\TicketWash;
use App\Models\WasherMovement;

class WasherReassignmentTipTest extends TestCase
{
    use RefreshDatabase;

    public function test_reassigning_washer_moves_tip()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $washerA = Washer::create(['name' => 'A', 'pending_amount' => 0, 'active' => true]);
        $washerB = Washer::create(['name' => 'B', 'pending_amount' => 0, 'active' => true]);

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
            'washer_id' => $washerA->id,
            'vehicle_id' => null,
            'vehicle_type_id' => null,
            'washer_paid' => false,
            'tip' => 20,
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
            'washer_id' => $washerA->id,
            'ticket_id' => $ticket->id,
            'amount' => 20,
            'description' => '[P] Test',
        ]);
        $washerA->increment('pending_amount', 120);

        $response = $this->actingAs($user)->put(route('tickets.update', $ticket), [
            'washers' => [ $wash->id => $washerB->id ],
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
        ]);

        $washerA->refresh();
        $washerB->refresh();
        $wash->refresh();

        $this->assertEquals(0, $washerA->pending_amount);
        $this->assertEquals(120, $washerB->pending_amount);
        $this->assertEquals($washerB->id, $wash->washer_id);
        $this->assertDatabaseMissing('washer_movements', [
            'washer_id' => $washerA->id,
            'ticket_id' => $ticket->id,
        ]);
        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washerB->id,
            'ticket_id' => $ticket->id,
            'amount' => 20,
        ]);
    }
}
