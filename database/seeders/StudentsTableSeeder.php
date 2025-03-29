<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsTableSeeder extends Seeder {
    public function run() {
        DB::table('students')->insert([
            // Мэдээллийн Технологи
            [
                'mail' => 'student1@example.com',
                'sisi_id' => '21B1NUM0000',
                'firstname' => 'Бат',
                'lastname' => 'Цэцэгээ',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 1, 
                'phone' => '99999999',
                'is_choosed' => true,
                'proposed_number' => 1
            ],
            [
                'mail' => 'student2@example.com',
                'sisi_id' => '21B1NUM0001',
                'firstname' => 'Сараа',
                'lastname' => 'Дөлгөөн',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 1, 
                'phone' => '88888888',
                'is_choosed' => true,
                'proposed_number' => 1
            ],
            [
                'mail' => 'student3@example.com',
                'sisi_id' => '21B1NUM0002',
                'firstname' => 'Түвшин',
                'lastname' => 'Баярсайхан',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 1, 
                'phone' => '77777777',
                'is_choosed' => false,
                'proposed_number' => 2
            ],
            [
                'mail' => 'student4@example.com',
                'sisi_id' => '21B1NUM0003',
                'firstname' => 'Дөлгөөн',
                'lastname' => 'Нарантуяа',
                'program' => 'Мэдээллийн Технологи',
                'dep_id' => 1, 
                'phone' => '66666666',
                'is_choosed' => true,
                'proposed_number' => 3
            ],

            // Программ Хангамж
            [
                'mail' => 'student5@example.com',
                'sisi_id' => '21B1NUM0004',
                'firstname' => 'Мөнх-Эрдэнэ',
                'lastname' => 'Ганболд',
                'program' => 'Программ Хангамж',
                'dep_id' => 1, 
                'phone' => '55555555',
                'is_choosed' => false,
                'proposed_number' => 1
            ],
            [
                'mail' => 'student6@example.com',
                'sisi_id' => '21B1NUM0005',
                'firstname' => 'Отгонбаяр',
                'lastname' => 'Тамир',
                'program' => 'Программ Хангамж',
                'dep_id' => 1, 
                'phone' => '44444444',
                'is_choosed' => true,
                'proposed_number' => 2
            ],
            [
                'mail' => 'student7@example.com',
                'sisi_id' => '21B1NUM0006',
                'firstname' => 'Энхжин',
                'lastname' => 'Даваажаргал',
                'program' => 'Программ Хангамж',
                'dep_id' => 1, 
                'phone' => '33333333',
                'is_choosed' => false,
                'proposed_number' => 1
            ],
            [
                'mail' => 'student8@example.com',
                'sisi_id' => '21B1NUM0007',
                'firstname' => 'Билгүүн',
                'lastname' => 'Төгсбилэг',
                'program' => 'Программ Хангамж',
                'dep_id' => 1, 
                'phone' => '22222222',
                'is_choosed' => true,
                'proposed_number' => 3
            ],

            // Компьютерийн Ухаан
            [
                'mail' => 'student9@example.com',
                'sisi_id' => '21B1NUM0008',
                'firstname' => 'Тэмүүлэн',
                'lastname' => 'Эрдэнэ',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1, 
                'phone' => '11111111',
                'is_choosed' => false,
                'proposed_number' => 2
            ],
            [
                'mail' => 'student10@example.com',
                'sisi_id' => '21B1NUM0009',
                'firstname' => 'Номин',
                'lastname' => 'Батсайхан',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1, 
                'phone' => '10101010',
                'is_choosed' => true,
                'proposed_number' => 3
            ],
            [
                'mail' => 'student11@example.com',
                'sisi_id' => '21B1NUM0010',
                'firstname' => 'Саруул',
                'lastname' => 'Энхжаргал',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1, 
                'phone' => '90909090',
                'is_choosed' => false,
                'proposed_number' => 1
            ],
            [
                'mail' => 'student12@example.com',
                'sisi_id' => '21B1NUM0011',
                'firstname' => 'Хонгор',
                'lastname' => 'Эрдэнэсүрэн',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1, 
                'phone' => '80808080',
                'is_choosed' => true,
                'proposed_number' => 2
            ]
        ]);
    }
}
