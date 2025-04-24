<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
            // $table->string('dep_id', 10);
            $table->json('fields');
            $table->date('created_date');
            $table->string('created_by', 10);
        
            // $table->foreign('dep_id')->references('id')->on('departments')->onDelete('cascade');
        });                      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal_forms');
    }
}
