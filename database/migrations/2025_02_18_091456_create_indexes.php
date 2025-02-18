<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('car_sticker', function (Blueprint $table) {
            // Add index for mem_id
            $table->index('mem_id');
            
            // Add index for timestamp
            $table->index('timestamp');
            
            // Add composite index for mem_id and timestamp
            $table->index(['mem_id', 'timestamp']);
        });
    }

    public function down()
    {
        Schema::table('car_sticker', function (Blueprint $table) {
            $table->dropIndex(['mem_id']);
            $table->dropIndex(['timestamp']);
            $table->dropIndex(['mem_id', 'timestamp']);
        });
    }
};