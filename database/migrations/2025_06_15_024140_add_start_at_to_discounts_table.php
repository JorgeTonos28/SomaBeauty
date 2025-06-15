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
        Schema::table('discounts', function (Blueprint $table) {
            if (!Schema::hasColumn('discounts', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            if (Schema::hasColumn('discounts', 'start_at')) {
                $table->dropColumn('start_at');
            }
        });
    }
};
