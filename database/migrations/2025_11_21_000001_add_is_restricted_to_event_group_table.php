<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_group', function (Blueprint $table) {
            // Determines if this specific group tag acts as an access barrier
            $table->boolean('is_restricted')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('event_group', function (Blueprint $table) {
            $table->dropColumn('is_restricted');
        });
    }
};
