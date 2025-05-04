<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisCyclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       //TODO REALLY NEED NAME
        Schema::create('thesis_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('year');
            $table->integer('end_year');
            $table->string('semester', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('grading_schema_id')->nullable()->constrained('grading_schemas')->onDelete('cascade');
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
            $table->string('status', 255)->nullable()->default('Хүлээгдэж буй'); //(Хүлээгдэж буй,Хаагдсан,Цуцлагдсан,Идэвхитэй, Устгах)
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
        Schema::dropIfExists('thesis_cycles');
    }
}
