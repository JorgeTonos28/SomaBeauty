<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('ticket_details_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
                $table->foreignId('ticket_wash_id')->nullable()->constrained('ticket_washes')->onDelete('cascade');
                $table->string('type');
                $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('drink_id')->nullable()->constrained()->onDelete('set null');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2);
                $table->string('description')->nullable();
                $table->timestamps();
            });
            DB::statement('INSERT INTO ticket_details_tmp SELECT * FROM ticket_details');
            Schema::drop('ticket_details');
            Schema::rename('ticket_details_tmp', 'ticket_details');
        } else {
            DB::statement("ALTER TABLE ticket_details MODIFY type ENUM('service','product','drink','extra')");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('ticket_details_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
                $table->foreignId('ticket_wash_id')->nullable()->constrained('ticket_washes')->onDelete('cascade');
                $table->string('type');
                $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('drink_id')->nullable()->constrained()->onDelete('set null');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2);
                $table->string('description')->nullable();
                $table->timestamps();
            });
            DB::statement("INSERT INTO ticket_details_tmp SELECT * FROM ticket_details");
            Schema::drop('ticket_details');
            Schema::rename('ticket_details_tmp', 'ticket_details');
        } else {
            DB::statement("ALTER TABLE ticket_details MODIFY type ENUM('service','product','drink')");
        }
    }
};
