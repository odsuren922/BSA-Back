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
        // Make sure departments exist first
        $departmentIds = DB::table('departments')->pluck('id')->toArray();
        
        if (empty($departmentIds)) {
            $this->command->error('No departments found in the database. Please seed departments first.');
            return;
        }

        $students = [
            // Students for Department 1 (Information Technology)
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0001',
                'firstname' => 'Мөнхтулга',
                'lastname' => 'Эрдэнэбилэг',
                'program' => 'Програм Хангамж',
                'mail' => 'munkhtulga.e@stud.num.edu.mn',
                'phone' => '99112233',
                'is_choosed' => true,
                'proposed_number' => 1,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0002',
                'firstname' => 'Түвшин',
                'lastname' => 'Баярсайхан',
                'program' => 'Мэдээллийн Системийн Удирдлага',
                'mail' => 'tuvshin.b@stud.num.edu.mn',
                'phone' => '99223344',
                'is_choosed' => false,
                'proposed_number' => 2,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 1,
                'sisi_id' => '21B1NUM0003',
                'firstname' => 'Ариунзаяа',
                'lastname' => 'Мөнхбат',
                'program' => 'Компьютерийн Сүлжээний Инженерчлэл',
                'mail' => 'ariunzaya.m@stud.num.edu.mn',
                'phone' => '99334455',
                'is_choosed' => true,
                'proposed_number' => 0,
                'gid' => '5',
                'role' => 'student'
            ],
            
            // Students for Department 2 (Business Administration)
            [
                'dep_id' => 2,
                'sisi_id' => '22B1NUM0004',
                'firstname' => 'Анхбаяр',
                'lastname' => 'Ганболд',
                'program' => 'Санхүү Банк',
                'mail' => 'ankhbayar.g@stud.num.edu.mn',
                'phone' => '99445566',
                'is_choosed' => true,
                'proposed_number' => 1,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 2,
                'sisi_id' => '22B1NUM0005',
                'firstname' => 'Оюунчимэг',
                'lastname' => 'Баатар',
                'program' => 'Маркетинг',
                'mail' => 'oyunchimeg.b@stud.num.edu.mn',
                'phone' => '99556677',
                'is_choosed' => false,
                'proposed_number' => 2,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 2,
                'sisi_id' => '22B1NUM0006',
                'firstname' => 'Батболд',
                'lastname' => 'Нямдорж',
                'program' => 'Нягтлан Бодох Бүртгэл',
                'mail' => 'batbold.n@stud.num.edu.mn',
                'phone' => '99667788',
                'is_choosed' => true,
                'proposed_number' => 0,
                'gid' => '5',
                'role' => 'student'
            ],
            
            // Students for Department 3 (Language and Literature)
            [
                'dep_id' => 3,
                'sisi_id' => '23B1NUM0007',
                'firstname' => 'Золжаргал',
                'lastname' => 'Доржпалам',
                'program' => 'Англи Хэл',
                'mail' => 'zoljargal.d@stud.num.edu.mn',
                'phone' => '99778899',
                'is_choosed' => true,
                'proposed_number' => 1,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 3,
                'sisi_id' => '23B1NUM0008',
                'firstname' => 'Болорчимэг',
                'lastname' => 'Энхбаяр',
                'program' => 'Монгол Хэл, Уран Зохиол',
                'mail' => 'bolorchimeg.e@stud.num.edu.mn',
                'phone' => '99889900',
                'is_choosed' => false,
                'proposed_number' => 0,
                'gid' => '5',
                'role' => 'student'
            ],
            [
                'dep_id' => 3,
                'sisi_id' => '23B1NUM0009',
                'firstname' => 'Энхжаргал',
                'lastname' => 'Батбаяр',
                'program' => 'Орчуулга Судлал',
                'mail' => 'enkhjargal.b@stud.num.edu.mn',
                'phone' => '99990011',
                'is_choosed' => true,
                'proposed_number' => 1,
                'gid' => '5',
                'role' => 'student'
            ]
        ];

        foreach ($students as $student) {
            // Check if student already exists to avoid duplicates
            $exists = DB::table('students')->where('sisi_id', $student['sisi_id'])->exists();
            
            if (!$exists) {
                DB::table('students')->insert($student);
            }
        }

        $this->command->info('Sample student data seeded successfully!');
    }
}