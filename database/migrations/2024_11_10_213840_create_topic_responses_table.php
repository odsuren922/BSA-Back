<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade');
            // $table->bigInteger('topic_id');
            //$table->bigInteger('supervisor_id', 10);
            $table->foreignId('supervisor_id')->constrained('supervisors')->onDelete('cascade');

            $table->bigInteger('res', 150);
            $table->date('res_date');

            //$table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
            //$table->foreign('supervisor_id')->references('id')->on('supervisors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topic_responses');
    }
}

