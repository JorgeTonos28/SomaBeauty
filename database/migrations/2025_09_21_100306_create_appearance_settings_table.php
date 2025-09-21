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
        Schema::create('appearance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->timestamp('logo_updated_at')->nullable();
            $table->timestamp('favicon_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appearance_settings');
    }
};
