<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('proposal_fields', function (Blueprint $table) {
            $table->id();
    
            $table->string('name');                // e.g., 'background'
            $table->string('name_en');   
            $table->text('description')->nullable();
    
            // thesis_cycle_id устсан
            $table->foreignId('dep_id')->constrained('departments')->onDelete('cascade');
    
            $table->string('type');               // e.g., 'text', 'textarea', 'date'
            $table->string('targeted_to');        // 'student', 'teacher', 'both'
            $table->boolean('is_required')->default(false);
    
            $table->string('status')->default('active'); // ← Шинэ
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
        Schema::dropIfExists('proposal_fields');
    }
}
