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
        // allow creating pending tickets without specifying payment method
        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() !== 'mysql') {
                return; // SQLite and others are ignored for simplicity
            }
            if (Schema::hasColumn('tickets', 'payment_method')) {
                DB::statement("ALTER TABLE tickets MODIFY payment_method ENUM('efectivo','tarjeta','transferencia','mixto') NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() !== 'mysql') {
                return;
            }
            if (Schema::hasColumn('tickets', 'payment_method')) {
                DB::statement("ALTER TABLE tickets MODIFY payment_method ENUM('efectivo','tarjeta','transferencia','mixto') NOT NULL");
            }
        });
    }
};
