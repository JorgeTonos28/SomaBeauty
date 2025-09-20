<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\VehicleType;
use App\Models\WasherMovement;
use App\Models\Ticket;
use App\Models\TicketWash;
use App\Models\TicketDetail;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class TicketEditTipExtraTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_ticket_updates_tip_and_extra_and_sets_tip_date(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $vehicleType = VehicleType::create(['name' => 'Carro']);
        $washer = Washer::create(['name' => 'Juan', 'pending_amount' => 0, 'active' => true]);
        $service = Service::create(['name' => 'Lavado', 'description' => 'test', 'active' => true]);
        ServicePrice::create(['service_id' => $service->id, 'vehicle_type_id' => $vehicleType->id, 'label' => 'Carro', 'price' => 200]);

        $date = Carbon::parse('2024-01-10');

        $vehicle = Vehicle::create([
            'customer_name' => 'Cliente',
            'vehicle_type_id' => $vehicleType->id,
            'plate' => 'ABC123',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'color' => 'Rojo',
            'year' => 2020,
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'customer_name' => 'Cliente',
            'customer_phone' => null,
            'total_amount' => 230,
            'paid_amount' => 0,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'pending' => true,
            'paid_at' => null,
            'created_at' => $date,
        ]);

        $wash = TicketWash::create([
            'ticket_id' => $ticket->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_type_id' => $vehicleType->id,
            'washer_id' => $washer->id,
            'washer_paid' => false,
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
            'unit_price' => 200,
            'discount_amount' => 0,
            'subtotal' => 200,
        ]);

        TicketDetail::create([
            'ticket_id' => $ticket->id,
            'ticket_wash_id' => null,
            'type' => 'extra',
            'service_id' => null,
            'product_id' => null,
            'drink_id' => null,
            'quantity' => 1,
            'unit_price' => 10,
            'discount_amount' => 0,
            'subtotal' => 10,
            'description' => 'Cargo',
        ]);

        Washer::whereId($washer->id)->increment('pending_amount', 120);
        WasherMovement::create([
            'washer_id' => $washer->id,
            'ticket_id' => $ticket->id,
            'amount' => 20,
            'description' => '[P] Toyota | Corolla | Rojo | 2020 | Carro',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $updateData = [
            'customer_name' => 'Cliente',
            'customer_phone' => null,
            'ticket_date' => $date->format('Y-m-d'),
            'washes' => [
                [
                    'vehicle_type_id' => $vehicleType->id,
                    'plate' => 'ABC123',
                    'brand' => 'Toyota',
                    'model' => 'Corolla',
                    'color' => 'Rojo',
                    'year' => 2020,
                    'washer_id' => $washer->id,
                    'service_ids' => [$service->id],
                    'tip' => 30,
                ],
            ],
            'charge_descriptions' => ['Cargo'],
            'charge_amounts' => [15],
            'ticket_action' => 'pay',
            'payment_method' => 'efectivo',
            'paid_amount' => 245,
            'bank_account_id' => null,
        ];

        $response = $this->put(route('tickets.update', $ticket), $updateData);
        $response->assertSessionHasNoErrors();
        $ticket->refresh();

        $this->assertEquals(200 + 30 + 15, $ticket->total_amount);
        $this->assertDatabaseHas('ticket_details', ['ticket_id' => $ticket->id, 'type' => 'extra', 'unit_price' => 15]);
        $this->assertDatabaseHas('ticket_washes', ['ticket_id' => $ticket->id, 'tip' => 30]);

        $washer->refresh();
        $this->assertEquals(100 + 30, $washer->pending_amount);
        $this->assertEquals(1, WasherMovement::where('washer_id', $washer->id)->where('ticket_id', $ticket->id)->where('description', 'like', '[P]%')->count());
        $movement = WasherMovement::where('washer_id', $washer->id)->where('ticket_id', $ticket->id)->first();
        $this->assertEquals(30, $movement->amount);
        $this->assertTrue($movement->created_at->isSameDay($date));
    }
}

