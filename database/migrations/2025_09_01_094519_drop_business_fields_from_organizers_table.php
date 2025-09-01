<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            if (Schema::hasColumn('organizers', 'business_name'))  $table->dropColumn('business_name');
            if (Schema::hasColumn('organizers', 'business_phone')) $table->dropColumn('business_phone');
            if (Schema::hasColumn('organizers', 'business_address')) $table->dropColumn('business_address');
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            // Recreate as nullable in case of rollback
            if (!Schema::hasColumn('organizers', 'business_name'))   $table->string('business_name')->nullable()->after('name');
            if (!Schema::hasColumn('organizers', 'business_phone'))  $table->string('business_phone')->nullable()->after('phone');
            if (!Schema::hasColumn('organizers', 'business_address'))$table->text('business_address')->nullable()->after('business_phone');
        });
    }
};

