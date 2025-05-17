<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            Schema::table('students', function (Blueprint $table) {
                $table->enum('status', ['active','dropped', 'graduated', 'suspended'])
                      ->default('active')
                      ->after('proposed_number');
            });
            //ACTIVE-> идэвхтэй суралцагч
            //DROPPED-> сургалтаас хасагдсан суралцагч
            //GRADUATED-> төгссөн суралцагч
            //SUSPENDED-> сургалтаас түр чөлөөлөгдсөн суралцагч
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
