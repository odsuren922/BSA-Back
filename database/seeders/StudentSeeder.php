<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->insert([
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0001',
                'firstname' => 'Сараа',
                'lastname' => 'Дөлгөөн',
                'program' => 'Мэдээллийн Технологи',
                'mail' => 'saraa@example.com',
                'phone' => '99999999',
                'is_choosed' => true,
                "proposed_number" => 0,
     
            ],
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0002',
                'firstname' => 'Түвшин',
                'lastname' => 'Баярсайхан',
                'program' => 'Програм Хангамж',
                'mail' => 'tuvshin@example.com',
                'phone' => '88888888',
                'is_choosed' => false,
                "proposed_number" => 0,
            
            ],
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0003',
                'firstname' => 'Мөнхтулга',
                'lastname' => 'Эрдэнэбилэг',
                'program' => 'Системийн Инженер',
                'mail' => ' tuvshin@example.com',
                'phone' => '88888888',
                'is_choosed' => false,
                "proposed_number" => 0,],
              
        ]);
    }
}
