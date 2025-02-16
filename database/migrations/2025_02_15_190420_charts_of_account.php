<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('charts_of_account', function (Blueprint $table) {
            $table->integer('acct_type_id')->primary();
            $table->string('acct_type', 45)->nullable();
            $table->string('acct_name', 45)->nullable();
            $table->string('acct_description', 45)->nullable();
            $table->datetime('timestamp')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('charts_of_account');
    }
};
