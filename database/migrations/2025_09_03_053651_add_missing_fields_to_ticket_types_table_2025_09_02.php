<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_types', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('ticket_types', 'description_ar')) {
                $table->text('description_ar')->nullable()->after('description');
            }
            if (!Schema::hasColumn('ticket_types', 'capacity')) {
                $table->integer('capacity')->nullable()->after('quantity_sold');
            }
            if (!Schema::hasColumn('ticket_types', 'is_limited')) {
                $table->boolean('is_limited')->default(true)->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $columns = ['name_ar', 'description_ar', 'capacity', 'is_limited'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ticket_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};