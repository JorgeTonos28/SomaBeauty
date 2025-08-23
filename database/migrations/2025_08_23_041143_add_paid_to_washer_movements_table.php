<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('washer_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('washer_movements', 'paid')) {
                $table->boolean('paid')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('washer_movements', function (Blueprint $table) {
            if (Schema::hasColumn('washer_movements', 'paid')) {
                $table->dropColumn('paid');
            }
        });
    }
};
