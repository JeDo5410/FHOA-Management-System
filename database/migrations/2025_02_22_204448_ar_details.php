<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ar_details', function (Blueprint $table) {
            $table->integer('ar_transno')->nullable();
            $table->string('payor_name', 45)->nullable();
            $table->string('payor_address', 100)->nullable();
            $table->string('mem_add_id', 10)->nullable();
            $table->integer('acct_type_id')->nullable();
            $table->decimal('ar_amount', 9, 2)->nullable();
            $table->decimal('arrear_bal', 9, 2)->nullable();
            $table->integer('user_id')->nullable();
            $table->datetime('timestamp')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ar_details');
    }
};