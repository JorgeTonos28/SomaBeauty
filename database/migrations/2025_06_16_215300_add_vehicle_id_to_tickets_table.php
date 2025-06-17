<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('washer_id')->constrained()->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'vehicle_id')) {
                $table->dropConstrainedForeignId('vehicle_id');
            }
        });
    }
};
