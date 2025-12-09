<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // "Selectable" means a regular user can opt-in/out.
            // If false (default), it's a "system/mandatory" role visible to everyone.
            $table->boolean('is_selectable')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('is_selectable');
        });
    }
};
