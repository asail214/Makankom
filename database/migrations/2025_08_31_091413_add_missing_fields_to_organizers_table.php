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
        Schema::table('organizers', function (Blueprint $table) {
            // Add missing fields that exist in the database but not in the original migration
            if (!Schema::hasColumn('organizers', 'business_name')) {
                $table->string('business_name')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('organizers', 'cr_number')) {
                $table->string('cr_number')->nullable()->after('business_name');
            }
            
            if (!Schema::hasColumn('organizers', 'business_address')) {
                $table->text('business_address')->nullable()->after('cr_number');
            }
            
            if (!Schema::hasColumn('organizers', 'business_phone')) {
                $table->string('business_phone')->nullable()->after('business_address');
            }
            
            if (!Schema::hasColumn('organizers', 'profile_img_url')) {
                $table->string('profile_img_url')->nullable()->after('business_phone');
            }
            
            if (!Schema::hasColumn('organizers', 'cr_document_path')) {
                $table->json('cr_document_path')->nullable()->after('profile_img_url');
            }
            
            if (!Schema::hasColumn('organizers', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'cr_number', 
                'business_address',
                'business_phone',
                'profile_img_url',
                'cr_document_path',
                'rejection_reason'
            ]);
        });
    }
};
