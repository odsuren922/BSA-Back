<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposedTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('proposed_topics', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('created_by_id');
            $table->string('created_by_type');
        

            $table->foreignId('thesis_cycle_id')->references('id')->on('thesis_cycles')->onDelete('set null');
            $table->foreignId('topic_content_id')->references('id')->on('topic_contents')->onDelete('cascade');
            $table->string('status')->default('draft');

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
        Schema::dropIfExists('proposed_topics');
    }
}
