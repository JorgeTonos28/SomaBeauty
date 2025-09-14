<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('petty_cash_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->date('effective_date');
            $table->timestamps();
        });

        // valor inicial por defecto
        DB::table('petty_cash_settings')->insert([
            'amount' => 3200,
            'effective_date' => '2020-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_settings');
    }
};
