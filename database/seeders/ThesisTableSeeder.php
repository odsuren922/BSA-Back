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
                'name_mongolian' => 'Дипломын ажлын удирдах систем: Төлөвлөгөө батлах, дүгнэх модуль',
                'name_english' => 'Thesis management system: Work plan and grading module',
                    'description' => 'Төлөвлөгөө батлуулах, явцын үнэлгээ оруулах, үзлэг болон хамгаалалтын хуваарь товлох, комиссын гишүүдийг хуваарилах, мэдээллэх модулийг хөгжүүлнэ.',
                'status' => 'active',
               
            ],

            [
               
                'supervisor_id' => 1,
                'student_id' => 2,
   
                'name_mongolian' => 'IoT in Smart Cities',
                    'name_english' => 'Ухаалаг хот дахь IoT технологи',
                    'description' => 'Enhancing urban infrastructure with IoT technology / Хотын дэд бүтцийг IoT технологиор сайжруулах'
           ,
                'status' => 'active',
                
            ],

            [
           
                'supervisor_id' => 1, 
                'student_id' => 3, 
                'name_mongolian' => 'Зээлийн хугацаа хэтрэлтийн шинжилгээ',
                    'name_english' => 'Data analysis of overdue loans',
                    'description' => '',
                'status' => 'active',
               
            ],

            [
           
                'supervisor_id' => 1, 
                'student_id' => 4, 
                'name_mongolian' => 'Блокчэйнд суурилсан гэрчилгээ баталгаажуулалтын систем',
                    'name_english' => 'Blockchain based certificate validation system',
                    'description' => 'Блокчэйн сонирхдог бол блокчэйн дээр баримт бичиг баталгаажуулах системийг хөгжүүлнэ. 
                    ReactJs, python мэддэг байвал сайн. Дипломын чвцад EVM solidity сурч smart contract хөгжүүлж сурна.',
                'status' => 'active',
               
            ],
        ]);
    }
}
