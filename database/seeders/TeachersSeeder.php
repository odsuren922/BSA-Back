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
         
                'mail' => 'supervisor1@example.com',
                'firstname' => 'Батаа',
                'lastname' => 'Болд',
                'dep_id' => '1', 
                //'numof_choosed_stud' => 5, 
                
            ],
            [ 
   
                'mail' => 'supervisor2@example.com',
                'firstname' => 'Нямаа',
                'lastname' => 'Сэндмаа',
                'dep_id' => '1',
            ],
            [
 
                'mail' => 'supervisor3@example.com',
                'firstname' => 'Хуягаа',
                'lastname' => 'Амараа',
                'dep_id' => '1',
            ],
        ]);
    }
}
