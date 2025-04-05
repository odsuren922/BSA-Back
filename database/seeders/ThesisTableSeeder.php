<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class ThesisTableSeeder extends Seeder
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
                    'supervisor_id' =>8, 
                    'student_id' => 191, 
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
