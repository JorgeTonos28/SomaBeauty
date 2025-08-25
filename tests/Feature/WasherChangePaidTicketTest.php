<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\Ticket;
use App\Models\TicketWash;
use App\Models\TicketDetail;
use App\Models\WasherMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasherChangePaidTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_change_washer_after_payment(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $washerA = Washer::create(['name' => 'A', 'pending_amount' => 0, 'active' => true]);
        $washerB = Washer::create(['name' => 'B', 'pending_amount' => 0, 'active' => true]);
        $vehicleType = VehicleType::create(['name' => 'Car']);
        $service = Service::create(['name' => 'Lavado', 'description' => 'Lavado', 'active' => true]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_id' => null,
            'customer_name' => 'Cliente',
            'customer_cedula' => null,
            'total_amount' => 0,
            'paid_amount' => 0,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'canceled' => false,
            'cancel_reason' => null,
            'pending' => false,
            'paid_at' => now(),
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => $washerA->id,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => true,
            'tip' => 10,
        ]);

        TicketDetail::create([
            'ticket_id' => $ticket->id,
            'ticket_wash_id' => $wash->id,
            'type' => 'service',
            'service_id' => $service->id,
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
            'amount' => 10,
            'description' => '[P] Test',
            'paid' => true,
        ]);

        $response = $this->actingAs($user)->from(route('tickets.index'))->put(route('tickets.update', $ticket), [
            'washers' => [ $wash->id => $washerB->id ],
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
        ]);

        $response->assertSessionHasErrors('washers');
        $this->assertEquals($washerA->id, $wash->fresh()->washer_id);
        $this->assertDatabaseMissing('washer_movements', [
            'washer_id' => $washerB->id,
            'ticket_id' => $ticket->id,
        ]);
    }
}
