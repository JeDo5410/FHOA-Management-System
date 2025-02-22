<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ar_details', function (Blueprint $table) {
            $table->integer('ar_transno')->primary();
            $table->integer('mem_id')->nullable();
            $table->integer('acct_type_id')->nullable();
            $table->decimal('ar_amount', 9, 2)->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_details');
    }
};