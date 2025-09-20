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

        DB::table('service_prices')
            ->join('vehicle_types', 'service_prices.vehicle_type_id', '=', 'vehicle_types.id')
            ->update(['label' => DB::raw('vehicle_types.name')]);
    }

    public function down(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
