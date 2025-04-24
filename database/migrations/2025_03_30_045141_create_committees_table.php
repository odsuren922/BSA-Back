<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitteesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('grading_component_id')->nullable()->constrained('grading_components')->nullOnDelete();
            $table->foreignId('dep_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('thesis_cycle_id')->nullable()->constrained('thesis_cycles')->cascadeOnDelete();
            $table->string('color')->nullable();
            $table->enum('status', ['planned', 'active', 'done', 'cancelled'])->default('planned');
           

            // $table->enum('status', ['төлөвлөгдсөн', 'идэвхтэй', 'дууссан', 'цуцлагдсан'])->default('төлөвлөгдсөн');
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
        Schema::dropIfExists('committees');
    }
}
