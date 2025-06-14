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
        Schema::table('ticket_details', function (Blueprint $table) {
            $table->foreignId('drink_id')->nullable()->after('product_id')->constrained()->onDelete('set null');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE ticket_details MODIFY type ENUM('service','product','drink')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_details', function (Blueprint $table) {
            $table->dropForeign(['drink_id']);
            $table->dropColumn('drink_id');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE ticket_details MODIFY type ENUM('service','product')");
        }
    }
};
