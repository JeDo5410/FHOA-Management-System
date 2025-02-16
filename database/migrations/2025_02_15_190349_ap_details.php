<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ap_details', function (Blueprint $table) {
            $table->integer('ap_transno')->nullable();
            $table->string('ap_particular', 45)->nullable();
            $table->decimal('ap_amount', 9, 2)->nullable();
            $table->integer('acct_type_id')->nullable();
            $table->datetime('timestamp')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ap_details');
    }
};
