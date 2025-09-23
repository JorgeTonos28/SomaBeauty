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
        Schema::table('appearance_settings', function (Blueprint $table) {
            $table->timestamp('qr_updated_at')->nullable()->after('favicon_updated_at');
            $table->string('qr_description')->nullable()->after('qr_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appearance_settings', function (Blueprint $table) {
            $table->dropColumn(['qr_updated_at', 'qr_description']);
        });
    }
};
