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
use App\Models\WasherPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TicketCancelRecoverPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_paid_ticket_creates_receivable_when_not_paying(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-02-01 10:00:00'));
        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create(['name' => 'Estilista', 'pending_amount' => 0, 'active' => true]);
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
            'washer_id' => $washer->id,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => true,
            'tip' => 20,
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

        $movement = WasherMovement::create([
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 20,
            'description' => '[P] Test',
            'paid' => true,
            'created_at' => now(),
        ]);
        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => now(),
            'total_washes' => 1,
            'amount_paid' => 120,
            'created_at' => now(),
        ]);

        $this->actingAs($user)->post(route('tickets.cancel', $ticket), [
            'cancel_reason' => 'test',
            'pay_washer' => 'no',
        ]);

        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => -100,
            'description' => 'Cuenta por cobrar - Ganancia de ticket cancelado',
        ]);
        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => -20,
            'description' => 'Cuenta por cobrar - Propina de ticket cancelado',
        ]);
        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 20,
            'description' => '[P] Test',
            'paid' => true,
        ]);
        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'canceled_ticket' => false,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'keep_commission_on_cancel' => false,
            'keep_tip_on_cancel' => false,
        ]);
        Carbon::setTestNow();
    }
}
