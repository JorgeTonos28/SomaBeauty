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

        $date = Carbon::yesterday()->toDateString();

        $response = $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'payment_date' => $date,
                'amount' => 200,
                'total_washes' => 2,
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

        Carbon::setTestNow();
    }
}

