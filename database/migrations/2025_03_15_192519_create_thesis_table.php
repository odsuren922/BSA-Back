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
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('teachers')->onDelete('cascade');

            $table->string('name_mongolian');
            $table->string('name_english');
            $table->text('description')->nullable();
            $table->enum('status', ['unactive','active', 'done', 'fail'])->default('active');
            $table->timestamps();

          
           

            // need to add dep_id
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