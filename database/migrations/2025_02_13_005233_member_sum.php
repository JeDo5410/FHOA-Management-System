<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('member_sum', function (Blueprint $table) {
            $table->integer('mem_id')->autoIncrement();
            $table->string('mem_add_id', 45)->nullable();
            $table->dateTime('arrear_month')->nullable();
            $table->decimal('arrear', 9, 2)->nullable();
            $table->integer('arrear_count')->nullable();
            $table->decimal('arrear_interest', 9, 2)->nullable();
            $table->string('last_salesinvoice', 45)->nullable();
            $table->string('last_paydate', 45)->nullable();
            $table->string('last_payamount', 45)->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_sum');
    }
};