<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thesis_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');
            $table->foreignId('grading_component_id')->constrained()->onDelete('cascade');
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('cascade'); // only if given_by = committee
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('cascade');   // who gave score
            $table->float('score');
            $table->text('comment')->nullable();
            $table->enum('given_by', ['supervisor', 'committee', 'teacher']);
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
        Schema::dropIfExists('thesis_scores');
    }
}
