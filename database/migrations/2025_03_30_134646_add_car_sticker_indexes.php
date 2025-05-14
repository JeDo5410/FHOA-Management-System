<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('car_sticker', function (Blueprint $table) {
            // This helps the first query that finds max timestamp
            $table->index(['mem_id', 'timestamp'], 'idx_car_memid_timestamp');
            
            // This helps the second query that filters by all three conditions
            $table->index(['mem_id', 'timestamp', 'vehicle_active'], 'idx_car_memid_timestamp_active');
        });

        Schema::table('member_data', function (Blueprint $table) {
            $table->index(['mem_id', 'mem_transno'], 'idx_memdata_memid_transno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
