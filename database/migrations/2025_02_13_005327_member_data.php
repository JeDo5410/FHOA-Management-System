<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('member_data', function (Blueprint $table) {
            $table->integer('mem_transno')->autoIncrement();
            $table->integer('mem_id')->nullable();
            $table->integer('mem_typecode')->nullable();
            $table->string('mem_name', 45)->nullable();
            $table->integer('mem_mobile')->nullable();
            $table->dateTime('mem_date')->nullable();
            $table->string('mem_email', 45)->nullable();
            $table->string('mem_SPA_Tenant', 45)->nullable();
            
            // Resident fields
            for ($i = 1; $i <= 10; $i++) {
                $table->string("mem_Resident{$i}", 45)->nullable();
                $table->string("mem_Relationship{$i}", 45)->nullable();
            }
            
            $table->string('mem_remarks', 100)->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            
            $table->foreign('mem_id')
                  ->references('mem_id')
                  ->on('member_sum');
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_data');
    }
};