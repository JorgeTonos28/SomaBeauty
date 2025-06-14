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
        Schema::table('washers', function (Blueprint $table) {
            if (Schema::hasColumn('washers', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('washers', function (Blueprint $table) {
            if (!Schema::hasColumn('washers', 'pending_amount')) {
                $table->decimal('pending_amount', 10, 2)->default(0)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('washers', function (Blueprint $table) {
            if (Schema::hasColumn('washers', 'pending_amount')) {
                $table->dropColumn('pending_amount');
            }
        });

        Schema::table('washers', function (Blueprint $table) {
            if (!Schema::hasColumn('washers', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }
        });
    }
};
