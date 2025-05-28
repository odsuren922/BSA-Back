<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicApprovalLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topic_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('proposed_topics')->onDelete('cascade');
            $table->foreignId('reviewer_id'); // багш, админ гэх мэт
            $table->string('reviewer_type'); // App\Models\Teacher, App\Models\Admin гэх мэт
        
            $table->enum('action', ['approved', 'rejected']);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        
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
        Schema::dropIfExists('topic_approval_logs');
    }
}
