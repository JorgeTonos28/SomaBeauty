<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\Ticket;
use App\Models\TicketWash;

class WasherUnassignedTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_washes_total_is_displayed()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 0,
            'active' => true,
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'efectivo',
            'washer_pending_amount' => 0,
            'customer_name' => 'Cliente',
            'pending' => true,
            'canceled' => false,
        ]);

        TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => null,
            'vehicle_id' => null,
            'vehicle_type_id' => null,
            'washer_paid' => false,
            'tip' => 50,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('washers.index', ['ajax' => 1]));

        $response->assertSeeInOrder([
            'Total adeudado',
            'RD$ 150.00',
            'Pendiente de asignar',
            'RD$ 150.00',
        ]);
    }
}
