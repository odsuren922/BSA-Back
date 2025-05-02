<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisDeadlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thesis_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('thesis_cycle_id')->constrained()->onDelete('cascade');

            // Role: student, supervisor, committee, assistant
            $table->string('target_people');
           // $table->enum('target_people', ['student', 'supervisor', 'committee', 'assistant']);

            $table->text('reminder_days')->nullable();

            //  name (e.g. 'submit_plan', 'upload_final_file', etc.)
            $table->string('name');
            $table->string('description')->nullable();

            // Optional link to grading_component (unelgeend zoriulsan deadline tohioldold)
            $table->foreignId('grading_component_id')->nullable()->constrained()->onDelete('set null');

            // Deadline date
            $table->date('deadline_date');
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
        Schema::dropIfExists('thesis_deadlines');
    }
}
