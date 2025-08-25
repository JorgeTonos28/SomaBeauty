<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\WasherPayment;
use App\Models\WasherMovement;
use App\Models\TicketWash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WasherDebtTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_paid_ticket_creates_washer_debt(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 10:00:00'));

        $user = User::factory()->create(['role' => 'admin']);

        $washer = Washer::create([
            'name' => 'Lavador',
            'pending_amount' => 0,
            'active' => true,
        ]);

        $vehicleType = VehicleType::create(['name' => 'Car']);
        $service = Service::create(['name' => 'Lavado', 'description' => 'Lavado', 'active' => true]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_id' => null,
            'customer_name' => 'Cliente',
            'customer_cedula' => null,
            'total_amount' => 200,
            'paid_amount' => 200,
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
            'washer_id' => $washer->id,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => false,
            'tip' => 0,
        ]);

        TicketDetail::create([
            'ticket_id' => $ticket->id,
            'ticket_wash_id' => $wash->id,
            'type' => 'service',
            'service_id' => $service->id,
            'product_id' => null,
            'drink_id' => null,
            'quantity' => 1,
            'unit_price' => 200,
            'discount_amount' => 0,
            'subtotal' => 200,
        ]);

        $washer->increment('pending_amount', 100);
        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => now(),
            'total_washes' => 1,
            'amount_paid' => 100,
        ]);
        $washer->update(['pending_amount' => 0]);

        $this->actingAs($user)
            ->post(route('tickets.cancel', $ticket), ['cancel_reason' => 'test']);

        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => -100,
            'description' => 'Cuenta por cobrar - Ganancia de ticket cancelado',
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . now()->toDateString() . '&end=' . now()->toDateString());
        $this->assertEquals(100, $response->viewData('accountsReceivable'));

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . now()->addDay()->toDateString() . '&end=' . now()->addDay()->toDateString());
        $this->assertEquals(0, $response->viewData('accountsReceivable'));

        Carbon::setTestNow();
    }
}
