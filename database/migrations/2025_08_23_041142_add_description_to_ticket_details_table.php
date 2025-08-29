<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_details', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_details', 'description')) {
                $table->string('description')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_details', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_details', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
