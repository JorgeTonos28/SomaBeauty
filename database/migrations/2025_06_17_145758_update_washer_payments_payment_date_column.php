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
        Schema::table('washer_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('payment_date');
            }
        });

        Schema::table('washer_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->timestamp('payment_date')->nullable();
            } else {
                $table->timestamp('payment_date')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('washer_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropColumn('payment_date');
            }
        });

        Schema::table('washer_payments', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->date('payment_date')->nullable();
            } else {
                $table->date('payment_date')->change();
            }
        });
    }
};
