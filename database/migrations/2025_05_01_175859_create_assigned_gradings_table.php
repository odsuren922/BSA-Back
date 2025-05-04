<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignedGradingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assigned_gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('thesis_cycle_id')->constrained()->cascadeOnDelete();
            // $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->morphs('assigned_by');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('thesis_id')->constrained('thesis')->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique([
                'grading_component_id',
                'assigned_by_type',
                'assigned_by_id',
                'student_id'
            ]);        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assigned_gradings');
    }
}
