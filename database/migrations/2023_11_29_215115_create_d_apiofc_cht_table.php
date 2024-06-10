<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (!Schema::hasTable('d_apiofc_cht'))
        {
            Schema::create('d_apiofc_cht', function (Blueprint $table) {
                $table->bigIncrements('d_apiofc_cht_id'); 
                $table->string('blobName')->nullable();//   "CHT.txt” 
                $table->string('blobType')->nullable();//   “text/plain”
                $table->text('blob')->nullable();//       “SE58SU5T”
                $table->string('size')->nullable();//       "32"
                $table->string('encoding')->nullable();//   “UTF-8”
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_apiofc_cht');
    }
};