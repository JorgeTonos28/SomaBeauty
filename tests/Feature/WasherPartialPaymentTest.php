<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Washer;
use App\Models\WasherPayment;

class WasherPartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_washer_can_be_paid_partially()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $washer = Washer::create([
            'name' => 'Test Washer',
            'pending_amount' => 300,
            'active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('washers.pay', $washer), [
                'payment_date' => '2024-01-01',
                'amount' => 200,
                'total_washes' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('washer_payments', [
            'washer_id' => $washer->id,
            'amount_paid' => 200,
            'total_washes' => 2,
        ]);

        $washer->refresh();
        $this->assertEquals(100, $washer->pending_amount);

        $payment = WasherPayment::first();
        $this->assertEquals('2024-01-01', $payment->payment_date->toDateString());
    }
}
