<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->string('label')->after('service_id');
        });

        DB::statement(
            "UPDATE service_prices
             SET label = (
                SELECT name
                FROM vehicle_types
                WHERE vehicle_types.id = service_prices.vehicle_type_id
             )"
        );
    }

    public function down(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
