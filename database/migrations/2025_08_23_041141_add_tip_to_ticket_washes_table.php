<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_washes', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_washes', 'tip')) {
                $table->decimal('tip', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_washes', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_washes', 'tip')) {
                $table->dropColumn('tip');
            }
        });
    }
};
