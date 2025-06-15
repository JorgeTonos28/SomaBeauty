<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('price');
        });
        Schema::table('washers', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('pending_amount');
        });
    }

    public function down(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->dropColumn('active');
        });
        Schema::table('washers', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
