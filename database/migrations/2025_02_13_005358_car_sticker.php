<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('car_sticker', function (Blueprint $table) {
            $table->integer('mem_id')->nullable();
            $table->integer('mem_code')->nullable();
            $table->string('vehicle_maker', 45)->nullable();
            $table->string('vehicle_type', 45)->nullable();
            $table->string('vehicle_color', 45)->nullable();
            $table->string('vehicle_OR', 45)->nullable();
            $table->string('vehicle_CR', 45)->nullable();
            $table->string('vehicle_plate', 45)->nullable();
            $table->string('car_sticker', 45)->nullable();
            $table->boolean('vehicle_active')->default(false);
            $table->string('remarks', 45)->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            
            $table->foreign('mem_id')
                  ->references('mem_id')
                  ->on('member_sum');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_sticker');
    }
};