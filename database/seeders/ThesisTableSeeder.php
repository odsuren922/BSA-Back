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
        DB::table('thesis')->insert([

            [
           
                'supervisor_id' => 1, 
                'student_id' => 1, 
                'name_mongolian' => 'Thesis management system: Work plan and grading module',
                    'name_english' => 'Дипломын ажлын удирдах систем: Төлөвлөгөө батлах, дүгнэх модуль',
                    'description' => 'өлөвлөгөө батлуулах, явцын үнэлгээ оруулах, үзлэг болон хамгаалалтын хуваарь товлох, комиссын гишүүдийг хуваарилах, мэдээллэх модулийг хөгжүүлнэ.',
                'status' => 'draft',
               
            ],

            [
               
                'supervisor_id' => 1,
                'student_id' => 2,
   
                'name_mongolian' => 'IoT in Smart Cities',
                    'name_english' => 'Ухаалаг хот дахь IoT технологи',
                    'description' => 'Enhancing urban infrastructure with IoT technology / Хотын дэд бүтцийг IoT технологиор сайжруулах'
           ,
                'status' => 'draft',
                
            ],
        ]);
    }
}
