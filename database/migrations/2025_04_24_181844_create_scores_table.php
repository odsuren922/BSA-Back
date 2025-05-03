<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('thesis_id')->nullable()->constrained('thesis')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('grading_components')->onDelete('cascade');
            
            $table->decimal('score', 5, 2);

            // Morph fields
            $table->string('given_by_type');
            $table->unsignedBigInteger('given_by_id');
            
            $table->foreignId('committee_student_id')->nullable()->constrained()->onDelete('set null');

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
        Schema::dropIfExists('scores');
    }
}
