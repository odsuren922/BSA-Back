<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grading_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_schema_id')->constrained('grading_schemas')->onDelete('cascade');
            $table->integer('order')->nullable();
            $table->decimal('score', 5, 2);
            $table->string('by_who', 255);
            $table->string('name', 255);
            $table->integer('scheduled_week')->default(0);

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
        Schema::dropIfExists('grading_components');
    }
}
