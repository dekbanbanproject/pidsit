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
        if (!Schema::hasTable('fdh_irf'))
        {
            Schema::connection('mysql')->create('fdh_irf', function (Blueprint $table) {
                $table->bigIncrements('fdh_irf_id');
 
                $table->string('AN',length: 15)->nullable();//  
                $table->string('REFER',length: 5)->nullable();//  
                $table->string('REFERTYPE',length: 1)->nullable(); //  
                
                $table->string('d_anaconda_id')->nullable(); //  
                $table->string('user_id')->nullable(); //  
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdh_irf');
    }
};
