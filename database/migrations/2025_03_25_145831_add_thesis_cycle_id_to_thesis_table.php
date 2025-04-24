<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thesis', function (Blueprint $table) {
            $table->foreignId('thesis_cycle_id')
                ->nullable()
                ->constrained('thesis_cycles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thesis', function (Blueprint $table) {
            $table->dropForeign(['thesis_cycle_id']);
            $table->dropColumn('thesis_cycle_id');
        });
    }
};
