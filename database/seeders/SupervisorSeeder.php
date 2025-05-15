<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupervisorSeeder extends Seeder
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

        $supervisors = [
            // Supervisors for Department 1 (Information Technology)
            [
                'id' => '1',
                'dep_id' => 1,
                'firstname' => 'Батбаяр',
                'lastname' => 'Очирсүрэн',
                'mail' => 'batbayar.o@num.edu.mn',
                'phone' => '99112244'
            ],
            [
                'id' => '2',
                'dep_id' => 1,
                'firstname' => 'Цэрэндорж',
                'lastname' => 'Гантулга',
                'mail' => 'tserendorj.g@num.edu.mn',
                'phone' => '99223355'
            ],
            [
                'id' => '3',
                'dep_id' => 1,
                'firstname' => 'Алтанзул',
                'lastname' => 'Баярсайхан',
                'mail' => 'altanzul.b@num.edu.mn',
                'phone' => '99334466'
            ],
            
            // Supervisors for Department 2 (Business Administration)
            [
                'id' => '4',
                'dep_id' => 2,
                'firstname' => 'Дэлгэрмаа',
                'lastname' => 'Энхбаяр',
                'mail' => 'delgermaa.e@num.edu.mn',
                'phone' => '99445577'
            ],
            [
                'id' => '5',
                'dep_id' => 2,
                'firstname' => 'Ганбат',
                'lastname' => 'Жаргалсайхан',
                'mail' => 'ganbat.j@num.edu.mn',
                'phone' => '99556688'
            ],
            [
                'id' => '6',
                'dep_id' => 2,
                'firstname' => 'Наранцэцэг',
                'lastname' => 'Цэрэндорж',
                'mail' => 'narantsetseg.ts@num.edu.mn',
                'phone' => '99667799'
            ],
            
            // Supervisors for Department 3 (Language and Literature)
            [
                'id' => '7',
                'dep_id' => 3,
                'firstname' => 'Оюунбилэг',
                'lastname' => 'Баасанжав',
                'mail' => 'oyunbileg.b@num.edu.mn',
                'phone' => '99778800'
            ],
            [
                'id' => '8',
                'dep_id' => 3,
                'firstname' => 'Энхтуяа',
                'lastname' => 'Баттулга',
                'mail' => 'enkhtuya.b@num.edu.mn',
                'phone' => '99889911'
            ],
            [
                'id' => '9',
                'dep_id' => 3,
                'firstname' => 'Мөнхбаяр',
                'lastname' => 'Оюунчимэг',
                'mail' => 'munkhbayar.o@num.edu.mn',
                'phone' => '99990022'
            ]
        ];

        foreach ($supervisors as $supervisor) {
            // Check if supervisor already exists to avoid duplicates
            $exists = DB::table('supervisors')->where('id', $supervisor['id'])->exists();
            
            if (!$exists) {
                DB::table('supervisors')->insert($supervisor);
            }
        }

        $this->command->info('Sample supervisor data seeded successfully!');
    }
}