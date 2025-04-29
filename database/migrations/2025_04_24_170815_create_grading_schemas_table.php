<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('grading_schemas', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->integer('step_num')->nullable();
            $table->string('name', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grading_schemas');
    }
}
