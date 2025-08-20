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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->dropColumn('name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('phone')->nullable()->after('date_of_birth');
            $table->string('profile_picture')->nullable()->after('phone');
            $table->text('address')->nullable()->after('profile_picture');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('postal_code');
            $table->dateTime('last_login_at')->nullable()->after('status');
            $table->string('preferred_language')->default('en')->after('last_login_at');
            $table->json('notification_preferences')->nullable()->after('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn([
                'first_name', 'last_name', 'gender', 'date_of_birth', 'phone', 
                'profile_picture', 'address', 'city', 'state', 'country', 
                'postal_code', 'status', 'last_login_at', 'preferred_language', 
                'notification_preferences'
            ]);
        });
    }
};
