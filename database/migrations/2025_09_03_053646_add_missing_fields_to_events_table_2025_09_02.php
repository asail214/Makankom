<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
            }
            if (!Schema::hasColumn('events', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
            if (!Schema::hasColumn('events', 'event_type')) {
                $table->enum('event_type', ['physical', 'virtual'])->default('physical')->after('description_ar');
            }
            if (!Schema::hasColumn('events', 'min_age')) {
                $table->integer('min_age')->nullable()->after('event_type');
            }
            if (!Schema::hasColumn('events', 'max_age')) {
                $table->integer('max_age')->nullable()->after('min_age');
            }
            if (!Schema::hasColumn('events', 'virtual_link')) {
                $table->string('virtual_link')->nullable()->after('venue_address');
            }
            if (!Schema::hasColumn('events', 'location_link')) {
                $table->string('location_link')->nullable()->after('virtual_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columns = ['title_ar', 'description_ar', 'event_type', 'min_age', 'max_age', 'virtual_link', 'location_link'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};