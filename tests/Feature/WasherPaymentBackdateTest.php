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

    public function test_washer_payment_uses_ticket_dates(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-08-19 12:00:00'));

        $user = User::factory()->create(['role' => 'admin']);

        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 200,
            'active' => true,
        ]);

        $ticket1 = \App\Models\Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer->id,
            'vehicle_type_id' => null,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'efectivo',
            'washer_pending_amount' => 100,
            'customer_name' => 'Cliente',
            'created_at' => Carbon::parse('2025-08-18 08:00:00'),
        ]);

        $ticket2 = \App\Models\Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer->id,
            'vehicle_type_id' => null,
            'total_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'efectivo',
            'washer_pending_amount' => 100,
            'customer_name' => 'Cliente',
            'created_at' => Carbon::parse('2025-08-17 09:00:00'),
        ]);

        $response = $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'ticket_ids' => $ticket1->id . ',' . $ticket2->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'payment_date' => '2025-08-18 08:00:00',
            'amount_paid' => 100,
        ]);

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'payment_date' => '2025-08-17 09:00:00',
            'amount_paid' => 100,
        ]);

        $this->assertDatabaseHas('washers', [
            'id' => $washer->id,
            'pending_amount' => 0,
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket1->id,
            'washer_pending_amount' => 0,
        ]);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket2->id,
            'washer_pending_amount' => 0,
        ]);

        Carbon::setTestNow();
    }
}

