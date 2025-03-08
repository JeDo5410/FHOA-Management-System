<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acct_receivable', function (Blueprint $table) {
            $table->integer('ar_transno')->autoIncrement();
            $table->integer('mem_transno')->nullable();
            $table->integer('or_number')->nullable();
            $table->date('ar_date')->nullable();
            $table->decimal('ar_total', 9, 2)->nullable();
            $table->string('ar_remarks', 45)->nullable();
            $table->string('receive_by', 45)->nullable();
            $table->string('payment_type', 20)->nullable();
            $table->string('payment_ref', 45)->nullable();
            $table->integer('user_id')->nullable();
            $table->datetime('timestamp')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acct_receivable');
    }
};