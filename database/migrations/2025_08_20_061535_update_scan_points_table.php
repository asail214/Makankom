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
        Schema::table('scan_points', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade')->after('status');
            $table->string('device_information')->nullable()->after('event_id');
            $table->string('location_details')->nullable()->after('device_information');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_points', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'device_information', 'location_details']);
        });
    }
};
