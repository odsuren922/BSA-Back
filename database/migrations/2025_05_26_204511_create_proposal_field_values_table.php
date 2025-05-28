<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('proposal_field_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proposed_topic_id')
                  ->constrained('proposed_topics')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('field_id');
            $table->foreign('field_id')->references('id')->on('proposal_fields')->onDelete('cascade');
            $table->enum('status', ['submitted', 'approved', 'declined'])->default('submitted');

            $table->text('value')->nullable();

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
        Schema::dropIfExists('proposal_field_values');
    }
}
