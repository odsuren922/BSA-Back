<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_cycle_id')->nullable()->constrained('thesis_cycles')->onDelete('cascade');
            $table->foreignId('component_id')->nullable()->constrained('grading_components')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target_type')->default('all');
            $table->timestamp('scheduled_at');
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('component_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders');
    }
}
