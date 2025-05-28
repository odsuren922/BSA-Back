<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalTopicRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal_topic_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('proposed_topics')->onDelete('cascade');
            $table->foreignId('requested_by_id'); 
            $table->string('requested_by_type');
            $table->text('req_note');
            $table->boolean('is_selected')->default(false);
            $table->timestamp('selected_at')->nullable();
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
        Schema::dropIfExists('proposal_topic_requests');
    }
}
