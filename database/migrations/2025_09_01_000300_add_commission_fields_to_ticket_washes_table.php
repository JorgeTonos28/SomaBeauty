<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_washes', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_washes', 'commission_percentage')) {
                $table->decimal('commission_percentage', 5, 2)->default(10)->after('tip');
            }
            if (! Schema::hasColumn('ticket_washes', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->default(0)->after('commission_percentage');
            }
        });

        if (Schema::hasColumn('ticket_washes', 'commission_percentage') && Schema::hasColumn('ticket_washes', 'commission_amount')) {
            DB::table('ticket_washes')->orderBy('id')->chunkById(100, function ($washes) {
                foreach ($washes as $wash) {
                    $detail = DB::table('ticket_details')
                        ->where('ticket_wash_id', $wash->id)
                        ->where('type', 'service')
                        ->select('subtotal')
                        ->first();

                    $subtotal = $detail ? (float) $detail->subtotal : 0.0;
                    $amount = round($subtotal * 0.10, 2);

                    DB::table('ticket_washes')
                        ->where('id', $wash->id)
                        ->update([
                            'commission_percentage' => 10,
                            'commission_amount' => $amount,
                        ]);
                }
            });

            DB::table('tickets')->orderBy('id')->chunkById(100, function ($tickets) {
                foreach ($tickets as $ticket) {
                    $pending = DB::table('ticket_washes')
                        ->where('ticket_id', $ticket->id)
                        ->whereNull('washer_id')
                        ->sum(DB::raw('commission_amount + tip'));

                    DB::table('tickets')
                        ->where('id', $ticket->id)
                        ->update(['washer_pending_amount' => $pending]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('ticket_washes', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_washes', 'commission_amount')) {
                $table->dropColumn('commission_amount');
            }
            if (Schema::hasColumn('ticket_washes', 'commission_percentage')) {
                $table->dropColumn('commission_percentage');
            }
        });
    }
};
