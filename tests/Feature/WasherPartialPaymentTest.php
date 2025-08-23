<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\WasherPayment;
use App\Models\Ticket;
use App\Models\TicketWash;
use Carbon\Carbon;

class WasherPartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_can_be_paid_partially()
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 10:00:00'));

        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 300,
            'active' => true,
        ]);

        $washes = [];
        for ($i = 0; $i < 3; $i++) {
            $ticket = Ticket::create([
                'user_id' => $user->id,
                'total_amount' => 0,
                'paid_amount' => 0,
                'payment_method' => 'efectivo',
                'washer_pending_amount' => 0,
                'customer_name' => 'Cliente',
            ]);
            $wash = TicketWash::create([
                'ticket_id' => $ticket->id,
                'washer_id' => $washer->id,
                'vehicle_id' => null,
                'vehicle_type_id' => null,
                'washer_paid' => false,
            ]);
            $washes[] = $wash;
        }

        $ids = collect($washes)->take(2)->pluck('id')->implode(',');

        Carbon::setTestNow(Carbon::parse('2024-01-10 12:00:00'));

        $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'wash_ids' => $ids,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'amount_paid' => 200,
            'total_washes' => 2,
        ]);

        $washer->refresh();
        $this->assertEquals(100, $washer->pending_amount);

        $this->assertDatabaseHas('ticket_washes', [
            'id' => $washes[0]->id,
            'washer_paid' => true,
        ]);
        $this->assertDatabaseHas('ticket_washes', [
            'id' => $washes[1]->id,
            'washer_paid' => true,
        ]);
        $this->assertDatabaseHas('ticket_washes', [
            'id' => $washes[2]->id,
            'washer_paid' => false,
        ]);

        $payment = WasherPayment::first();
        $this->assertEquals('2024-01-01', $payment->payment_date->toDateString());

        Carbon::setTestNow();
    }
}
