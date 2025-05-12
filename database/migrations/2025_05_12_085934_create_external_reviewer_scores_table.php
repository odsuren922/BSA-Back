<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalReviewerScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_reviewer_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_reviewer_id')->constrained('external_reviewers')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('grading_component_id')->nullable()->constrained('grading_components')->onDelete('cascade');
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
        Schema::dropIfExists('external_reviewer_scores');
    }
}
