<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisPlanStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thesis_plan_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');

            // Status flags
            $table->boolean('student_sent')->default(false);
            $table->enum('teacher_status', ['approved', 'returned', 'pending'])->default('pending');
            $table->enum('department_status', ['approved', 'returned', 'pending'])->default('pending');

            // Timestamps for each status change
            $table->timestamp('student_sent_at')->nullable();
            $table->timestamp('teacher_status_updated_at')->nullable();
            $table->timestamp('department_status_updated_at')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thesis_plan_status');
    }
}
