<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grading_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_component_id')->constrained('grading_components')->onDelete('cascade');
            $table->string('name', 255);
            $table->decimal('score', 5, 2);
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
        Schema::dropIfExists('grading_criterias');
    }
}
