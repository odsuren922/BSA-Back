<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
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

        $teachers = [
            // Teachers for Department 1 (Information Technology)
            [
                'id' => '1',
                'dep_id' => 1,
                'firstname' => 'Баатарбилэг',
                'lastname' => 'Амаржаргал',
                'degree' => 'Доктор',
                'superior' => 'Тэнхимийн эрхлэгч',
                'mail' => 'baatarbileg.a@num.edu.mn',
                'numof_choosed_stud' => 2,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '2',
                'dep_id' => 1,
                'firstname' => 'Мөнхтулга',
                'lastname' => 'Батбаяр',
                'degree' => 'Магистр',
                'superior' => 'Багш',
                'mail' => 'munkhtulga.b@num.edu.mn',
                'numof_choosed_stud' => 1,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '3',
                'dep_id' => 1,
                'firstname' => 'Оюунбилэг',
                'lastname' => 'Цэрэндорж',
                'degree' => 'Профессор',
                'superior' => 'Ахлах багш',
                'mail' => 'oyunbileg.ts@num.edu.mn',
                'numof_choosed_stud' => 3,
                'gid' => '8',
                'role' => 'teacher'
            ],
            
            // Teachers for Department 2 (Business Administration)
            [
                'id' => '4',
                'dep_id' => 2,
                'firstname' => 'Энхбаяр',
                'lastname' => 'Ганбаатар',
                'degree' => 'Доктор',
                'superior' => 'Тэнхимийн эрхлэгч',
                'mail' => 'enkhbayar.g@num.edu.mn',
                'numof_choosed_stud' => 2,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '5',
                'dep_id' => 2,
                'firstname' => 'Солонго',
                'lastname' => 'Дамдинсүрэн',
                'degree' => 'Профессор',
                'superior' => 'Ахлах багш',
                'mail' => 'solongo.d@num.edu.mn',
                'numof_choosed_stud' => 3,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '6',
                'dep_id' => 2,
                'firstname' => 'Баярсайхан',
                'lastname' => 'Энхтүвшин',
                'degree' => 'Магистр',
                'superior' => 'Багш',
                'mail' => 'bayarsaikhan.e@num.edu.mn',
                'numof_choosed_stud' => 1,
                'gid' => '8',
                'role' => 'teacher'
            ],
            
            // Teachers for Department 3 (Language and Literature)
            [
                'id' => '7',
                'dep_id' => 3,
                'firstname' => 'Болорчимэг',
                'lastname' => 'Баатар',
                'degree' => 'Доктор',
                'superior' => 'Тэнхимийн эрхлэгч',
                'mail' => 'bolorchimeg.b@num.edu.mn',
                'numof_choosed_stud' => 2,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '8',
                'dep_id' => 3,
                'firstname' => 'Нарансолонго',
                'lastname' => 'Отгонбаяр',
                'degree' => 'Магистр',
                'superior' => 'Багш',
                'mail' => 'naransolongo.o@num.edu.mn',
                'numof_choosed_stud' => 1,
                'gid' => '8',
                'role' => 'teacher'
            ],
            [
                'id' => '9',
                'dep_id' => 3,
                'firstname' => 'Энхтүвшин',
                'lastname' => 'Жаргалсайхан',
                'degree' => 'Профессор',
                'superior' => 'Ахлах багш',
                'mail' => 'enkhtuwshin.j@num.edu.mn',
                'numof_choosed_stud' => 3,
                'gid' => '8',
                'role' => 'teacher'
            ]
        ];

        foreach ($teachers as $teacher) {
            // Check if teacher already exists to avoid duplicates
            $exists = DB::table('teachers')->where('id', $teacher['id'])->exists();
            
            if (!$exists) {
                DB::table('teachers')->insert($teacher);
            }
        }

        $this->command->info('Sample teacher data seeded successfully!');
    }
}