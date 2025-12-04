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
        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' to 'username'
            $table->renameColumn('name', 'username');

            // Add calendar-specific fields
            $table->string('phone_number')->nullable()->after('email');
            $table->string('profile_picture')->nullable()->after('phone_number');

            // KEY CHANGE: Added ->nullable() here
            $table->date('birth_date')->nullable()->after('profile_picture');

            // Add foreign keys to supporting tables
            $table->foreignId('country_id')->nullable()->after('birth_date')
                ->constrained()->nullOnDelete();
            $table->foreignId('zipcode_id')->nullable()->after('country_id')
                ->constrained()->nullOnDelete();
            $table->foreignId('gender_id')->nullable()->after('zipcode_id')
                ->constrained()->nullOnDelete();

            // Add active status
            $table->boolean('is_active')->default(true)->after('gender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['country_id']);
            $table->dropForeign(['zipcode_id']);
            $table->dropForeign(['gender_id']);

            // Drop columns
            $table->dropColumn([
                'phone_number',
                'profile_picture',
                'birth_date',
                'country_id',
                'zipcode_id',
                'gender_id',
                'is_active',
            ]);

            // Rename back to original
            $table->renameColumn('username', 'name');
        });
    }
};
