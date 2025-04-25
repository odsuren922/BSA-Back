<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThesisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('thesis')->insert(
            [

                [
                    'supervisor_id' =>1, 
                    'student_id' => 1, 
                    'name_mongolian' => 'Нүүр царай таних систем',
                    'name_english' => 'Facial Recognition System',
                    'description' => 'Камерын тусламжтайгаар хэрэглэгчийн нүүр царайг таних систем хөгжүүлэх.',
                    'status' => 'active',
                    'thesis_cycle_id' => 1
                ],

            ]
            
        );
    }
}
