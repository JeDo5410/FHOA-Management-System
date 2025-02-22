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
        Schema::create('acct_receivable', function (Blueprint $table) {
            $table->integer('ar_transno')->autoIncrement();
            $table->string('mem_transno', 45)->nullable();
            $table->integer('or_number')->nullable();
            $table->date('ar_date')->nullable();
            $table->decimal('ar_total', 9, 2)->nullable();
            $table->string('ar_remarks', 45)->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acct_receivable');
    }
};