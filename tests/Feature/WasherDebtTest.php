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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasherDebtTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_paid_ticket_creates_washer_debt(): void
    {
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
            'washer_id' => $washer->id,
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

        TicketDetail::create([
            'ticket_id' => $ticket->id,
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
    }
}
