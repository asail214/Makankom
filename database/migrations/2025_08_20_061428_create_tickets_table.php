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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->enum('status', ['active', 'used', 'cancelled', 'refunded'])->default('active');
            $table->string('qr_code')->unique();
            $table->dateTime('used_at')->nullable();
            $table->foreignId('used_by_scan_point')->nullable()->constrained('scan_points')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['ticket_number']);
            $table->index(['customer_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index(['qr_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
