<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitteeScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
     public function up()
     {
         Schema::create('committee_scores', function (Blueprint $table) {
             $table->id();
 
             // Foreign key to final score entry
             $table->foreignId('score_id')->constrained('scores')->onDelete('cascade');
             $table->foreignId('thesis_id')->nullable()->constrained('thesis')->cascadeOnDelete();
             $table->foreignId('student_id')->constrained()->onDelete('cascade');
             $table->foreignId('committee_member_id')->constrained()->onDelete('cascade');
             $table->foreignId('component_id')->constrained('grading_components')->onDelete('cascade');
 
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
        Schema::dropIfExists('committee_scores');
    }
}
