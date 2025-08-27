<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'keep_commission_on_cancel')) {
                $table->boolean('keep_commission_on_cancel')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'keep_tip_on_cancel')) {
                $table->boolean('keep_tip_on_cancel')->nullable();
            }
        });

        Schema::table('washer_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('washer_payments', 'canceled_ticket')) {
                $table->boolean('canceled_ticket')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'keep_commission_on_cancel')) {
                $table->dropColumn('keep_commission_on_cancel');
            }
            if (Schema::hasColumn('tickets', 'keep_tip_on_cancel')) {
                $table->dropColumn('keep_tip_on_cancel');
            }
        });

        Schema::table('washer_payments', function (Blueprint $table) {
            if (Schema::hasColumn('washer_payments', 'canceled_ticket')) {
                $table->dropColumn('canceled_ticket');
            }
        });
    }
};
