<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThesisFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thesis_files', function (Blueprint $table) {
            $table->id();
       
            $table->foreignId('thesis_id')->constrained('thesis')->onDelete('cascade');

            $table->string('file_path');
            $table->string('original_name');
            $table->string('type')->nullable(); // final, draft, presentation etc
            $table->foreignId('uploaded_by')->constrained('students');
            
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); 
            $table->foreignId('approved_by')->nullable()->constrained('teachers'); 
            $table->timestamp('approved_at')->nullable();
    
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
        Schema::dropIfExists('thesis_files');
    }
}
