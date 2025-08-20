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
            $table->enum('type', ['individual', 'company', 'organization'])->default('individual')->after('business_name');
            $table->string('cr_number')->nullable()->after('type');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null')->after('status');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['type', 'cr_number', 'approved_by', 'approved_at', 'rejection_reason']);
        });
    }
};
