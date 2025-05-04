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
            $table->foreignId('thesis_cycle_id')->constrained('thesis_cycles')->onDelete('cascade');
            $table->string('target_people'); // Role: student, supervisor, committee, assistant
            $table->json('reminder_days')->nullable(); // Days before deadline to send reminders
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('grading_component_id')->nullable()->constrained('grading_components')->onDelete('set null');
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