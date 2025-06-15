<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discount_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('discount_logs', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('discount_logs', 'end_at')) {
                $table->dateTime('end_at')->nullable()->after('start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_logs', function (Blueprint $table) {
            if (Schema::hasColumn('discount_logs', 'start_at')) {
                $table->dropColumn('start_at');
            }
            if (Schema::hasColumn('discount_logs', 'end_at')) {
                $table->dropColumn('end_at');
            }
        });
    }
};
