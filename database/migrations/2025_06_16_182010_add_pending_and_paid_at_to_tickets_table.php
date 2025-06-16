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
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'pending')) {
                $table->boolean('pending')->default(false)->after('canceled');
            }
            if (!Schema::hasColumn('tickets', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('pending');
            }
        });

        DB::table('tickets')->whereNull('paid_at')->update(['paid_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('tickets', 'pending')) {
                $table->dropColumn('pending');
            }
        });
    }
};
