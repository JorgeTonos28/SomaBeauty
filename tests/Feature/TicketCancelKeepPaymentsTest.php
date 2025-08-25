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

class TicketCancelKeepPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_paid_ticket_keeps_payments_when_requested(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 10:00:00'));
        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create(['name' => 'Lavador', 'pending_amount' => 0, 'active' => true]);
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
            'tip' => 15,
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
            'amount' => 15,
            'description' => '[P] Test',
            'paid' => true,
            'created_at' => now(),
        ]);
        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => now(),
            'total_washes' => 1,
            'amount_paid' => 115,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('tickets.cancel', $ticket), [
            'cancel_reason' => 'test',
            'pay_commission' => 1,
            'pay_tip' => 1,
        ]);

        $washer->refresh();
        $this->assertEquals(0, $washer->pending_amount);
        $this->assertDatabaseHas('washer_movements', [
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 15,
            'description' => '[P] Test',
            'paid' => true,
        ]);
        $this->assertDatabaseMissing('washer_movements', [
            'description' => 'Cuenta por cobrar - Ganancia de ticket cancelado',
            'ticket_id' => $ticket->id,
        ]);
        $this->assertDatabaseMissing('washer_movements', [
            'description' => 'Cuenta por cobrar - Propina de ticket cancelado',
            'ticket_id' => $ticket->id,
        ]);

        $dash = $this->actingAs($user)->get('/dashboard?start=2024-01-01&end=2024-01-01');
        $this->assertEquals(0, $dash->viewData('accountsReceivable'));

        Carbon::setTestNow();
    }
}
