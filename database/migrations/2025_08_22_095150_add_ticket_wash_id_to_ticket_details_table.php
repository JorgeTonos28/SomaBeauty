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
        Schema::table('ticket_details', function (Blueprint $table) {
            $table->foreignId('ticket_wash_id')->nullable()->after('ticket_id')
                ->constrained('ticket_washes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_wash_id');
        });
    }
};
