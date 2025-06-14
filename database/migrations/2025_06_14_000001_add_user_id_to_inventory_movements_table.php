<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('product_id')->constrained()->onDelete('set null');
            $table->dropColumn('description');
        });
    }

    public function down()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
