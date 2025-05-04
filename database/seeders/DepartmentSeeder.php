<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            [
                'id' => 1,
                'name' => 'Мэдээллийн Технологи',
                'programs' => json_encode([
                    ['id' => 'IT101', 'name' => 'Програм Хангамж', 'index' => 1],
                    ['id' => 'IT102', 'name' => 'Мэдээллийн Системийн Удирдлага', 'index' => 2],
                    ['id' => 'IT103', 'name' => 'Компьютерийн Сүлжээний Инженерчлэл', 'index' => 3]
                ])
            ],
            [
                'id' => 2,
                'name' => 'Бизнесийн Удирдлага',
                'programs' => json_encode([
                    ['id' => 'BUS101', 'name' => 'Санхүү Банк', 'index' => 1],
                    ['id' => 'BUS102', 'name' => 'Маркетинг', 'index' => 2],
                    ['id' => 'BUS103', 'name' => 'Нягтлан Бодох Бүртгэл', 'index' => 3]
                ])
            ],
            [
                'id' => 3,
                'name' => 'Хэл, Уран Зохиол',
                'programs' => json_encode([
                    ['id' => 'LANG101', 'name' => 'Англи Хэл', 'index' => 1],
                    ['id' => 'LANG102', 'name' => 'Монгол Хэл, Уран Зохиол', 'index' => 2],
                    ['id' => 'LANG103', 'name' => 'Орчуулга Судлал', 'index' => 3]
                ])
            ]
        ];

        foreach ($departments as $department) {
            // Check if department already exists to avoid duplicates
            $exists = DB::table('departments')->where('id', $department['id'])->exists();
            
            if (!$exists) {
                DB::table('departments')->insert($department);
            }
        }

        $this->command->info('Sample department data seeded successfully!');
    }
}