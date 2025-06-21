<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\WasherPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class CashTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_payment_reduces_cash_total(): void
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
            'paid_at' => $date,
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

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . $date->toDateString() . '&end=' . $date->toDateString());

        $this->assertEquals(200, $response->viewData('cashTotal'));

        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => $date,
            'total_washes' => 1,
            'amount_paid' => 100,
            'observations' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard?start=' . $date->toDateString() . '&end=' . $date->toDateString());

        $this->assertEquals(100, $response->viewData('cashTotal'));
    }
}

