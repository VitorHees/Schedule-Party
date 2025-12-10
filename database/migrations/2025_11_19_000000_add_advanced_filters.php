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
        // 1. Add Filter Columns to Events
        Schema::table('events', function (Blueprint $table) {
            // Check if columns exist before adding to prevent errors during fresh migrations
            if (!Schema::hasColumn('events', 'max_distance_km')) {
                $table->integer('max_distance_km')->nullable()->after('location');
            }

            if (!Schema::hasColumn('events', 'min_age')) {
                $table->integer('min_age')->nullable()->after('max_distance_km');
            }

            if (!Schema::hasColumn('events', 'event_zipcode')) {
                $table->string('event_zipcode')->nullable()->after('location');
            }

            if (!Schema::hasColumn('events', 'event_country_id')) {
                $table->foreignId('event_country_id')->nullable()->constrained('countries')->nullOnDelete()->after('event_zipcode');
            }

            if (!Schema::hasColumn('events', 'is_role_restricted')) {
                $table->boolean('is_role_restricted')->default(true)->after('visibility_rule')
                    ->comment('If true, only users with the assigned role can see it. If false, visible to all.');
            }
        });

        // 2. [REMOVED] Zipcodes update
        // We removed the block adding latitude/longitude to zipcodes
        // because your create_zipcodes_table migration already handles it.

        // 3. Create Event-Gender Pivot (Many-to-Many)
        if (!Schema::hasTable('event_gender')) {
            Schema::create('event_gender', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gender_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_gender');

        Schema::table('events', function (Blueprint $table) {
            // Only drop if they exist
            if (Schema::hasColumn('events', 'event_country_id')) {
                $table->dropForeign(['event_country_id']);
                $table->dropColumn('event_country_id');
            }

            $columnsToDrop = ['min_age', 'event_zipcode', 'is_role_restricted', 'max_distance_km'];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
