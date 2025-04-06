<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateScheduleDatetimeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['date', 'start_time', 'end_time']); // remove old
            $table->dateTime('start_datetime')->after('event_type');
            $table->dateTime('end_datetime')->nullable()->after('start_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['start_datetime', 'end_datetime']); // rollback
    
            // Add back old fields
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
        });
    }
}
