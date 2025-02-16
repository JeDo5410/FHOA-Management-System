<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acct_payable', function (Blueprint $table) {
            $table->integer('ap_transno')->autoIncrement();
            $table->integer('ap_voucherno')->nullable();
            $table->date('ap_date')->nullable();
            $table->string('ap_payee', 45)->nullable();
            $table->string('ap_paytype', 45)->nullable();
            $table->string('paytype_reference', 45)->nullable();
            $table->decimal('ap_total', 9, 2)->nullable();
            $table->string('remarks', 45)->nullable();
            $table->integer('user_id')->nullable();
            $table->datetime('timestamp')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acct_payable');
    }
};