<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmailNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // email_notifications table
        DB::statement("ALTER TABLE email_notifications ALTER COLUMN scheduled_at TYPE TIMESTAMPTZ USING scheduled_at::timestamptz");
        DB::statement("ALTER TABLE email_notifications ALTER COLUMN sent_at TYPE TIMESTAMPTZ USING sent_at::timestamptz");
      

        // email_notification_recipients table
        DB::statement("ALTER TABLE email_notification_recipients ALTER COLUMN sent_at TYPE TIMESTAMPTZ USING sent_at::timestamptz");
        DB::statement("ALTER TABLE email_notification_recipients ALTER COLUMN opened_at TYPE TIMESTAMPTZ USING opened_at::timestamptz");
      
    }

    public function down()
    {
        // Revert to timestamp without time zone
        DB::statement("ALTER TABLE email_notifications ALTER COLUMN scheduled_at TYPE TIMESTAMP USING scheduled_at::timestamp");
        DB::statement("ALTER TABLE email_notifications ALTER COLUMN sent_at TYPE TIMESTAMP USING sent_at::timestamp");
     

        DB::statement("ALTER TABLE email_notification_recipients ALTER COLUMN sent_at TYPE TIMESTAMP USING sent_at::timestamp");
        DB::statement("ALTER TABLE email_notification_recipients ALTER COLUMN opened_at TYPE TIMESTAMP USING opened_at::timestamp");
 
    }
}
