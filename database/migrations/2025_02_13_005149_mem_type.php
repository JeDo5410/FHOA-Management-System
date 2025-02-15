<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mem_type', function (Blueprint $table) {
            $table->integer('mem_typecode')->primary();
            $table->string('mem_typedescription', 45)->nullable();
            $table->decimal('mem_monthlydues', 9, 2)->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });

        // Insert default member types
        $memberTypes = [
            [
                'mem_typecode' => 0,
                'mem_typedescription' => 'Home Owners'
            ],
            [
                'mem_typecode' => 1,
                'mem_typedescription' => 'Tenant'
            ],
            [
                'mem_typecode' => 2,
                'mem_typedescription' => 'SPA'
            ],
            [
                'mem_typecode' => 3,
                'mem_typedescription' => 'Home Owners Lot'
            ]
        ];

        DB::table('mem_type')->insert($memberTypes);
    }

    public function down()
    {
        Schema::dropIfExists('mem_type');
    }
};
