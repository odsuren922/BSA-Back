<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('teachers')->insert([
            [
         
                'mail' => 'supervisor18@example.com',
                'firstname' => 'Батаа',
                'lastname' => 'Болд',
                'dep_id' => '1', 
                
            ],
            [ 
   
                'mail' => 'supervisor19@example.com',
                'firstname' => 'Нямаа',
                'lastname' => 'Сэндмаа',
                'dep_id' => '1',
            ],
            [
 
                'mail' => 'supervisor20@example.com',
                'firstname' => 'Хуягаа',
                'lastname' => 'Амараа',
                'dep_id' => '1',
            ],
            [
                'mail' => 'supervisor21@example.com',
                'firstname' => 'Ганхуяг',
                'lastname' => 'Дашдондог',
                'dep_id' => '1',
            ],
            [
                'mail' => 'supervisor22@example.com',
                'firstname' => 'Цэцгээ',
                'lastname' => 'Отгон',
                'dep_id' => '1',
            ],
            [
                'mail' => 'supervisor23@example.com',
                'firstname' => 'Дорж',
                'lastname' => 'Содном',
                'dep_id' => '1',
            ],
            [
                'mail' => 'supervisor24@example.com',
                'firstname' => 'Бямба',
                'lastname' => 'Энхжаргал',
                'dep_id' => '1',
            ],
            // [
            //     'mail' => 'admin1@example.com',
            //     'firstname' => 'Бямба',
            //     'lastname' => 'Амаржаргал',
            //     'dep_id' => '1',
            // ],
            // [
            //     'mail' => 'admin-@example.com',
            //     'firstname' => 'Амаржаргал',
            //     'lastname' => 'Пүрэвсүрэг',
            //     'dep_id' => '1',
            // ],
            [
                'mail' => 'supervisor25@example.com',
                'firstname' => 'Бямба',
                'lastname' => 'Пүрэвсүрэг',
                'dep_id' => '1',
            ],
        ]);
    }
}
