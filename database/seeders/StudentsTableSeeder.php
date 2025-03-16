<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsTableSeeder extends Seeder {
    public function run() {
        DB::table('students')->insert([
            [
                'mail' => 'student1@example.com',
                'sisi_id' => '21B1NUM0000',
                'firstname' => 'Бат',
                'lastname' => 'Цэцэгээ',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 'D001', 
                'phone' =>'99999999',
                'is_choosed'=> true,
                'proposed_number'=> 1
            ],
            [
                'mail' => 'student2@example.com',
                'sisi_id' => '21B1NUM0001',
                'firstname' => 'Сараа',
                'lastname' => 'Дөлгөөн',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 'D001', 
                'phone' =>'99999999',
                'is_choosed'=> true,
                'proposed_number'=> 1
            ]
        ]);
    }
}
