<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('thesis', function (Blueprint $table) {
            $table->id();
           // $table->string('supervisor_id', 10);
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('teachers')->onDelete('cascade');
            
           // $table->json('topic'); 
            $table->timestamps();
            //TODO:: THESIS STATUS
            $table->enum('status', ['draft', 'sent_to_teacher', 'approved_by_teacher','cancelled_by_teacher','sent_to_dep', 'approved_by_dep', 'cancelled_by_dep'])
          ->default('draft');
       
            $table->string('name_mongolian')->nullable();
            $table->string('name_english')->nullable();
            $table->text('description')->nullable();

            $table->timestamp('submitted_to_teacher_at')->nullable();
           // $table->timestamp('approved_by_teacher_at')->nullable();
            $table->timestamp('submitted_to_dep_at')->nullable();
            //$table->timestamp('approved_by_dep_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis');
    }
}
