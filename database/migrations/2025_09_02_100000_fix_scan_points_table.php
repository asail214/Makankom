<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_points', function (Blueprint $table) {
            $table->string('token')->unique()->after('device_information');
            $table->string('location')->nullable()->after('token');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('scan_points', function (Blueprint $table) {
            $table->dropColumn(['token', 'location', 'status']);
        });
    }
};