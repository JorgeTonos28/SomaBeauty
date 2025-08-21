<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Washer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class WasherPaymentBackdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_payment_uses_selected_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-08-19 12:00:00'));

        $user = User::factory()->create(['role' => 'admin']);

        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 200,
            'active' => true,
        ]);

        $tickets = [];
        for ($i = 0; $i < 2; $i++) {
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

        $date = Carbon::yesterday()->toDateString();

        $response = $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'payment_date' => $date,
                'ticket_ids' => collect($tickets)->pluck('id')->implode(','),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'payment_date' => $date . ' 12:00:00',
            'amount_paid' => 200,
        ]);

        $this->assertDatabaseHas('washers', [
            'id' => $washer->id,
            'pending_amount' => 0,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $tickets[0]->id,
            'washer_pending_amount' => 0,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $tickets[1]->id,
            'washer_pending_amount' => 0,
        ]);

        Carbon::setTestNow();
    }
}

