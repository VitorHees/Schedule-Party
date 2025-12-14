<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // is_private: If true, regular members cannot opt-in (Invite Only).
            // Only applies if is_selectable is true.
            $table->boolean('is_private')->default(false)->after('is_selectable');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });
    }
};
