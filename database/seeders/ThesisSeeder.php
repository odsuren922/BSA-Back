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
                [
                    'student_id' => 2,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Хиймэл оюун ухаанд суурилсан сургалтын систем',
                    'name_english' => 'AI-Based Learning System',
                    'description' => 'Machine Learning ашиглан сурагчдад тохирсон сургалтын систем хөгжүүлэх',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                
                ],
                [
                    'student_id' => 3,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Үг бүтээх тоглоом ',
                    'name_english' => 'Flutter Word Puzzle Game',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],
                [
                    'student_id' => 4,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Ярианаас сэтгэл зүйн байдал таних',
                    'name_english' => 'Recognizing Emotional States Using Speech
Information',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],
                [
                    'student_id' => 5,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'OpenGL ашиглан процедур газрын гадарга үүсгэх ',
                    'name_english' => 'Procedural Terrain Generation with OpenGL',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],
                [
                    'student_id' => 6,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'RTC технологид суурилсан дуудлагын аппликейшн ',
                    'name_english' => 'RTC based call application ',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],
                [
                    'student_id' => 7,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Машин түрээсийн веб сайт  ',
                    'name_english' => 'Car renting website ',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],
                [
                    'student_id' => 8,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Сагсан бөмбөгийн мэргэжлийн тамирчин бэлтгэлийн
гүйцэтгэлээ хөтлөх веб аппликейшн  ',
                    'name_english' => 'A web application for professional basketball players
to record and monitor their training performance',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],

                [
                    'student_id' => 9,
                    'supervisor_id' => 1,
                    'name_mongolian' => 'Сав суулгыг гүн сургалтын аргаар ангилах  ',
                    'name_english' => 'Pottery classification using deep learning ',
                    'description' => 'Санхүүгийн үйлчилгээнд блокчейн ашиглах боломжууд',
                    'status' => 'active',
                    'thesis_cycle_id' => 1,
                ],


            ]
            
        );
    }
}
