<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Objetivo: convertir o crear washer_payments.payment_date como TIMESTAMP NULL
     * evitando el uso de ->change().
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (Schema::hasColumn('washer_payments', 'payment_date')) {
                Schema::table('washer_payments', function (Blueprint $table) {
                    $table->dropColumn('payment_date');
                });
            }

            Schema::table('washer_payments', function (Blueprint $table) {
                $table->timestamp('payment_date')->nullable();
            });

            return;
        }

        // MySQL/MariaDB
        try {
            DB::statement("ALTER TABLE `washer_payments` MODIFY `payment_date` TIMESTAMP NULL");
        } catch (Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (strpos($msg, 'unknown column') !== false || strpos($msg, "doesn't exist") !== false) {
                Schema::table('washer_payments', function (Blueprint $table) {
                    $table->timestamp('payment_date')->nullable();
                });
            } else {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Objetivo: revertir a DATE NULL sin usar ->change().
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (Schema::hasColumn('washer_payments', 'payment_date')) {
                Schema::table('washer_payments', function (Blueprint $table) {
                    $table->dropColumn('payment_date');
                });
            }

            Schema::table('washer_payments', function (Blueprint $table) {
                $table->date('payment_date')->nullable();
            });

            return;
        }

        // MySQL/MariaDB
        try {
            DB::statement("ALTER TABLE `washer_payments` MODIFY `payment_date` DATE NULL");
        } catch (Throwable $e) {
            $msg = strtolower($e->getMessage());
            if (strpos($msg, 'unknown column') !== false || strpos($msg, "doesn't exist") !== false) {
                Schema::table('washer_payments', function (Blueprint $table) {
                    $table->date('payment_date')->nullable();
                });
            } else {
                throw $e;
            }
        }
    }
};
