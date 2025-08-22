<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\WasherPayment;
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

        $tickets = [];
        for ($i = 0; $i < 3; $i++) {
            $tickets[] = \App\Models\Ticket::create([
                'user_id' => $user->id,
                'washer_id' => $washer->id,
                'vehicle_type_id' => null,
                'total_amount' => 0,
                'paid_amount' => 0,
                'payment_method' => 'efectivo',
                'washer_pending_amount' => 100,
                'customer_name' => 'Cliente',
            ]);
        }

        $ids = collect($tickets)->take(2)->pluck('id')->implode(',');

        Carbon::setTestNow(Carbon::parse('2024-01-10 12:00:00'));

        $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'ticket_ids' => $ids,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'amount_paid' => 200,
            'total_washes' => 2,
        ]);

        $washer->refresh();
        $this->assertEquals(100, $washer->pending_amount);

        $this->assertDatabaseHas('tickets', [
            'id' => $tickets[0]->id,
            'washer_pending_amount' => 0,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $tickets[1]->id,
            'washer_pending_amount' => 0,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $tickets[2]->id,
            'washer_pending_amount' => 100,
        ]);

        $payment = WasherPayment::first();
        $this->assertEquals('2024-01-01', $payment->payment_date->toDateString());

        Carbon::setTestNow();
    }
}
