<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisCycleDeadlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   
     public function up(): void
     {
         Schema::create('thesis_cycle_deadlines', function (Blueprint $table) {
             $table->id();
             $table->foreignId('thesis_cycle_id')->constrained()->onDelete('cascade');
             $table->string('type'); // 'student_submission', 'teacher_scoring', 'grading_component'
             $table->unsignedBigInteger('related_id')->nullable(); // e.g. grading_component_id
             $table->string('title')->nullable();
             $table->text('description')->nullable();
             $table->date('start_date')->nullable();
             $table->date('end_date')->nullable();
             $table->time('start_time')->nullable();
             $table->time('end_time')->nullable();
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
        Schema::dropIfExists('thesis_cycle_deadlines');
    }
}
