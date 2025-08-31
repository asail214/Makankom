<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('brands', 'slug')) {
                $table->dropUnique(['slug']); // Drop unique constraint first
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('brands', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('brands', 'website')) {
                $table->dropColumn('website');
            }
            if (Schema::hasColumn('brands', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('brands', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('brands', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('brands', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Add back removed fields
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }
};