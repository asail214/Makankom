<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_points', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // ERD uses 'label' not 'name'
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // Required in ERD
            $table->string('device_information')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_points');
    }
};