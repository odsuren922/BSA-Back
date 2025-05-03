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

            $table->foreignId('thesis_cycle_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Role: student, supervisor, committee, assistant
            $table->string('role');

            // Task name (e.g. 'submit_plan', 'upload_final_file', etc.)
            $table->string('task');

            // Optional link to grading_component (unelgeend zoriulsan deadline tohioldold)
            $table->foreignId('grading_component_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null');

            // Deadline date
            $table->date('deadline');

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
