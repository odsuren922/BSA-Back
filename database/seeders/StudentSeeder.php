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
    
                ['dep_id' => 1, 'sisi_id' => '21B1NUM1175', 'firstname' => 'Адъяатөмөр', 'lastname' => 'Нам.', 'program' => 'Компьютерийн ухаан', 'mail' => 'adya@example.com', 'phone' => '88888001', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM1724', 'firstname' => 'Алтантуул', 'lastname' => 'Бат.', 'program' => 'Компьютерийн ухаан', 'mail' => 'altan@example.com', 'phone' => '88888002', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM0220', 'firstname' => 'Анхбаяр', 'lastname' => 'Бая.', 'program' => 'Компьютерийн ухаан', 'mail' => 'ankh@example.com', 'phone' => '88888003', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '17B1NUM2239', 'firstname' => 'Бадрал', 'lastname' => 'Бая.', 'program' => 'Компьютерийн ухаан', 'mail' => 'badral@example.com', 'phone' => '88888004', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM0031', 'firstname' => 'Балжинням', 'lastname' => 'Бат.', 'program' => 'Компьютерийн ухаан', 'mail' => 'baljin@example.com', 'phone' => '88888005', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '22B1NUM1148', 'firstname' => 'Бат-Эрдэнэ', 'lastname' => 'Цэн.', 'program' => 'Компьютерийн ухаан', 'mail' => 'baterdene@example.com', 'phone' => '88888006', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM0421', 'firstname' => 'Батсайхан', 'lastname' => 'Гот.', 'program' => 'Компьютерийн ухаан', 'mail' => 'batsaikhan@example.com', 'phone' => '88888007', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM2553', 'firstname' => 'Батхүлэг', 'lastname' => 'Сан.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bathuleg@example.com', 'phone' => '88888008', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '19B1NUM0271', 'firstname' => 'Баяндалай', 'lastname' => 'Өлз.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bayandalai@example.com', 'phone' => '88888009', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '16B1SEAS1441', 'firstname' => 'Баярхүү', 'lastname' => 'Тэг.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bayarkhuu@example.com', 'phone' => '88888010', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM0857', 'firstname' => 'Баясгалан', 'lastname' => 'Туу.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bayasaa@example.com', 'phone' => '88888011', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM1083', 'firstname' => 'Билгүүн', 'lastname' => 'Бар.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bilguun@example.com', 'phone' => '88888012', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM2494', 'firstname' => 'Билгүүндалай', 'lastname' => 'Бат.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bilguundalai@example.com', 'phone' => '88888013', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '19B1NUM0390', 'firstname' => 'Билгүүнжаргал', 'lastname' => 'Эрд.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bilguunjar@example.com', 'phone' => '88888014', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM1683', 'firstname' => 'Билэгжаргал', 'lastname' => 'Шин.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bilegt@example.com', 'phone' => '88888015', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM1604', 'firstname' => 'Булган', 'lastname' => 'Бор.', 'program' => 'Компьютерийн ухаан', 'mail' => 'bulgan@example.com', 'phone' => '88888016', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM0082', 'firstname' => 'Буян-Учрал', 'lastname' => 'Ама.', 'program' => 'Компьютерийн ухаан', 'mail' => 'buyanaa@example.com', 'phone' => '88888017', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM2491', 'firstname' => 'Бэлгүдэй', 'lastname' => 'Бол.', 'program' => 'Компьютерийн ухаан', 'mail' => 'belgud@example.com', 'phone' => '88888018', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM1780', 'firstname' => 'Гандуулга', 'lastname' => 'Ган.', 'program' => 'Компьютерийн ухаан', 'mail' => 'ganduulga@example.com', 'phone' => '88888019', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '20B1NUM1789', 'firstname' => 'Давааням', 'lastname' => 'Дор.', 'program' => 'Компьютерийн ухаан', 'mail' => 'davaanyam@example.com', 'phone' => '88888020', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM1068', 'firstname' => 'Далай-Очир', 'lastname' => 'Бүр.', 'program' => 'Компьютерийн ухаан', 'mail' => 'dalai@example.com', 'phone' => '88888021', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '18B1NUM1221', 'firstname' => 'Дорж', 'lastname' => 'Хор.', 'program' => 'Компьютерийн ухаан', 'mail' => 'dorj@example.com', 'phone' => '88888022', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '21B1NUM1302', 'firstname' => 'Дэчиннямбуу', 'lastname' => 'Түв.', 'program' => 'Компьютерийн ухаан', 'mail' => 'dechin@example.com', 'phone' => '88888023', 'is_choosed' => false, 'proposed_number' => 0],
                ['dep_id' => 1, 'sisi_id' => '19B1NUMXXXX', 'firstname' => 'Золжаргал', 'lastname' => 'Бям.', 'program' => 'Компьютерийн ухаан', 'mail' => 'zoloo@example.com', 'phone' => '88888024', 'is_choosed' => false, 'proposed_number' => 0],
 
            
              
        ]);
    }
}