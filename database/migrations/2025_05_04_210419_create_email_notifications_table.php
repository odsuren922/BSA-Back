<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('content');
            $table->string('target_type'); // 'student', 'teacher', 'department', etc.
            $table->json('target_criteria')->nullable(); // Criteria for selecting recipients
            $table->json('metadata')->nullable(); // Additional data
            $table->datetime('scheduled_at')->nullable(); // When to send
            $table->datetime('sent_at')->nullable(); // When it was actually sent
            $table->string('status')->default('pending'); // pending, scheduled, sent, failed
            $table->text('status_message')->nullable(); // For errors or info
            $table->string('created_by_id');
            $table->string('created_by_type');
            $table->timestamps();
        });
        
        Schema::create('email_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('email_notifications')->onDelete('cascade');
            $table->string('recipient_id'); // User/Student/Teacher ID
            $table->string('recipient_type'); // Type of recipient (student, teacher, etc)
            $table->string('email');
            $table->string('status')->default('pending'); // pending, sent, failed, opened
            $table->datetime('sent_at')->nullable();
            $table->datetime('opened_at')->nullable();
            $table->text('status_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_notification_recipients');
        Schema::dropIfExists('email_notifications');
    }
}