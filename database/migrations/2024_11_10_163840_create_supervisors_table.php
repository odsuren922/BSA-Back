<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupervisorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            // $table->string('dep_id', 10);
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
            $table->string('firstname', 40);
            $table->string('lastname', 40);
            $table->string('mail', 100);
            $table->string('phone', 12);
        
            // $table->foreign('dep_id')->references('id')->on('departments')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supervisors');
    }
}
