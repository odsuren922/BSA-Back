<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('departments')->insert([
            [
            
                'name'=>"Мэдээллийн технологи, электроникийн сургууль",
               // 'programs'=> json_encode(['Компьютерын Ухаан', 'Мэдээллийн технологи', 'Програм хангамж']),
                

            ],
        ]);
    }
}
