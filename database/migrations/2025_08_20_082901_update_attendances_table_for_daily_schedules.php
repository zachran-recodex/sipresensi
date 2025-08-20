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
        Schema::table('attendances', function (Blueprint $table) {
            // Remove the old time and work_days columns
            $table->dropColumn(['clock_in_time', 'clock_out_time', 'work_days']);

            // Add new JSON column for daily schedules
            $table->json('daily_schedules')->nullable()->after('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Remove the new column
            $table->dropColumn('daily_schedules');

            // Restore old columns
            $table->time('clock_in_time')->after('location_id');
            $table->time('clock_out_time')->after('clock_in_time');
            $table->json('work_days')->after('clock_out_time');
        });
    }
};
