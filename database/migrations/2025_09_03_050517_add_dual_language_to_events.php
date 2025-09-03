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
        Schema::table('events', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
            $table->enum('event_type', ['physical', 'virtual'])->default('physical')->after('description_ar');
            $table->integer('min_age')->nullable()->after('event_type');
            $table->integer('max_age')->nullable()->after('min_age');
            $table->string('virtual_link')->nullable()->after('venue_address');
            $table->string('location_link')->nullable()->after('virtual_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'title_ar', 'description_ar', 'event_type', 
                'min_age', 'max_age', 'virtual_link', 'location_link'
            ]);
        });
    }
};