<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketWash;

class WasherUnassignedPaidTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_unassigned_wash_counts_towards_totals()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'efectivo',
            'washer_pending_amount' => 150,
            'customer_name' => 'Cliente',
            'pending' => false,
            'canceled' => false,
            'paid_at' => now(),
        ]);

        TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => null,
            'vehicle_id' => null,
            'vehicle_type_id' => null,
            'washer_paid' => false,
            'tip' => 50,
        ]);

        $date = now()->toDateString();
        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('washers.index', ['start' => $date, 'end' => $date, 'ajax' => 1]));

        $response->assertSee('RD$ 150.00');

        $dash = $this->actingAs($user)
            ->get("/dashboard?start=$date&end=$date");

        $this->assertEquals(150, $dash->viewData('washerPayDue'));
    }
}
