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
        Schema::table('invitations', function (Blueprint $table) {
            // Allow permanent links
            $table->timestamp('expires_at')->nullable()->change();

            // Track successful joins
            $table->integer('usage_count')->default(0)->after('click_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable(false)->change();
            $table->dropColumn('usage_count');
        });
    }
};
