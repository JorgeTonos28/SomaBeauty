<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('washer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('washer_id')->constrained()->onDelete('cascade');
            $table->date('payment_date');
            $table->integer('total_washes');
            $table->decimal('amount_paid', 10, 2);
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('washer_payments');
    }
};
