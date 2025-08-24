<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Remove fields not in ERD
            $table->dropColumn([
                'slug', 'description', 'website', 'email', 
                'phone', 'address', 'is_active'
            ]);
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