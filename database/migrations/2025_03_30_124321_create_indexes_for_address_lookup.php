<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add index to mem_add_id for LIKE searches
        Schema::table('member_sum', function (Blueprint $table) {
            $table->index('mem_add_id', 'idx_mem_add_id');
        });
        
        // Add index to mem_id for joins
        Schema::table('member_sum', function (Blueprint $table) {
            $table->index('mem_id', 'idx_mem_id');
        });
        
        // Add composite index to member_data for the subquery that finds max transactions
        Schema::table('member_data', function (Blueprint $table) {
            $table->index(['mem_id', 'mem_transno'], 'idx_mem_id_transno');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_sum', function (Blueprint $table) {
            $table->dropIndex('idx_mem_add_id');
            $table->dropIndex('idx_mem_id');
        });
        
        Schema::table('member_data', function (Blueprint $table) {
            $table->dropIndex('idx_mem_id_transno');
        });
    }
};