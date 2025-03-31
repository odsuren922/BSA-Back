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
           
                'supervisor_id' => 2, 
                'student_id' => 11, 
                'name_mongolian' => 'hДипломын ажлын удирдах систем: Төлөвлөгөө батлах, дүгнэх модуль',
                'name_english' => 'Thehhhsis manshshsagement system: Work plan and grading module',
                    'description' => 'Төлөвjsjjsлөгөө батлуулах, явцын үнэлгээ оруулах, үзлэг болон хамгаалалтын хуваарь товлох, комиссын гишүүдийг хуваарилах, мэдээллэх модулийг хөгжүүлнэ.',
                'status' => 'active',
               
            ],

            [
               
                'supervisor_id' => 3,
                'student_id' => 12,
   
                'name_mongolian' => 'hIoT in Smart Cities',
                    'name_english' => 'hhУхаалагshshs хот дахь IoT технологи',
                    'description' => 'Enhanjsjjscing urban infrastructure with IoT technology / Хотын дэд бүтцийг IoT технологиор сайжруулах'
           ,
                'status' => 'active',
                
            ],

            [
           
                'supervisor_id' => 4, 
                'student_id' => 13, 
                'name_mongolian' => 'hЗээлийн хугацаа хэтрэлтийн шинжилгээ',
                    'name_english' => 'sssData analysis of overdue loans',
           'description' => 'hhh',
         'status' => 'active',
               
            ],

            [
           
                'supervisor_id' => 1, 
                'student_id' => 14, 
                'name_mongolian' => 'hБлокчэйнд суурилсан гэрчилгээ баталгаажуулалтын систем',
                    'name_english' => 'Blockchaishshsn based certificate validation system',
                    'description' => 'Блокчjsjjsэйн сонирхдог бол блокчэйн дээр баримт бичиг баталгаажуулах системийг хөгжүүлнэ. 
                    ReactJs, python мэддэг байвал сайн. Дипломын чвцад EVM solidity сурч smart contract хөгжүүлж сурна.',
                'status' => 'active',
               
            ],
        ]);
    }
}
