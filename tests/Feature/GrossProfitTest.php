<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\WasherPayment;
use App\Models\TicketWash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class GrossProfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_payment_does_not_affect_gross_profit(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 0,
            'active' => true,
        ]);

        $vehicleType = VehicleType::create(['name' => 'Car']);
        $service = Service::create([
            'name' => 'Lavado',
            'description' => 'Lavado',
            'active' => true,
        ]);

        $date = Carbon::today();

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
            'paid_at' => $date,
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => $washer->id,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => false,
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

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . $date->toDateString() . '&end=' . $date->toDateString());

        $grossProfit = $response->viewData('grossProfit');
        $this->assertEquals(100, $grossProfit);
        $this->assertEquals(0, $response->viewData('accountsReceivable'));

        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => $date,
            'total_washes' => 1,
            'amount_paid' => 100,
            'observations' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . $date->toDateString() . '&end=' . $date->toDateString());

        $this->assertEquals(100, $response->viewData('grossProfit'));
        $this->assertEquals(0, $response->viewData('accountsReceivable'));
    }

    public function test_pending_ticket_with_assigned_washer_reduces_gross_profit(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $washer = Washer::create([
            'name' => 'Estilista',
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
            'paid_amount' => 0,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'canceled' => false,
            'cancel_reason' => null,
            'pending' => true,
            'paid_at' => null,
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => $washer->id,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => false,
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

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . now()->toDateString() . '&end=' . now()->toDateString());

        $this->assertEquals(-100, $response->viewData('grossProfit'));
    }

    public function test_pending_ticket_without_washer_does_not_affect_gross_profit(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $vehicleType = VehicleType::create(['name' => 'Car']);
        $service = Service::create(['name' => 'Lavado', 'description' => 'Lavado', 'active' => true]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_id' => null,
            'customer_name' => 'Cliente',
            'customer_cedula' => null,
            'total_amount' => 200,
            'paid_amount' => 0,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 100,
            'canceled' => false,
            'cancel_reason' => null,
            'pending' => true,
            'paid_at' => null,
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'washer_id' => null,
            'vehicle_id' => null,
            'vehicle_type_id' => $vehicleType->id,
            'washer_paid' => false,
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

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . now()->toDateString() . '&end=' . now()->toDateString());

        $this->assertEquals(0, $response->viewData('grossProfit'));
        $this->assertEquals(100, $response->viewData('washerPayDue'));
    }
}

