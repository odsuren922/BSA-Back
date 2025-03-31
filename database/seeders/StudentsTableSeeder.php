<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsTableSeeder extends Seeder {
    public function run() {
        DB::table('students')->insert([
            // Компьютерийн Ухаан
            [
                'mail' => 'student11120@12email.com',
                'sisi_id' => '21F1NUM1111',
                'firstname' => 'Оюутан1',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81111',
                'is_choosed' => true,
                'proposed_number' => 2
           ],
            [
                'mail' => 'student2120@12email.com',
                'sisi_id' => '21F1NUM1112',
                'firstname' => 'Оюутан2',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81112',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student3120@12email.com',
                'sisi_id' => '21F1NUM2123',
                'firstname' => 'Оюутан3',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81113',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student4120@12email.com',
                'sisi_id' => '21F1NUM1114',
                'firstname' => 'Оюутан4',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81114',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student5120@12email.com',
                'sisi_id' => '21F1NUM2215',
                'firstname' => 'Оюутан5',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81115',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student6120@12email.com',
                'sisi_id' => '21F1NUM1116',
                'firstname' => 'Оюутан6',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81116',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student7120@12email.com',
                'sisi_id' => '21F1NUM1117',
                'firstname' => 'Оюутан7',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81117',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student8120@12email.com',
                'sisi_id' => '21F1NUM1118',
                'firstname' => 'Оюутан8',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81118',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student9120@12email.com',
                'sisi_id' => '21F1NUM1119',
                'firstname' => 'Оюутан9',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81119',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student10120@12email.com',
                'sisi_id' => '21F1NUM1010',
                'firstname' => 'Оюутан10',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110010',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student11120@12email.com',
                'sisi_id' => '21F1NUM1011',
                'firstname' => 'Оюутан11',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110011',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student12120@12email.com',
                'sisi_id' => '21F1NUM1012',
                'firstname' => 'Оюутан12',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110012',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student13120@12email.com',
                'sisi_id' => '21F1NUM1113',
                'firstname' => 'Оюутан13',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110013',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student14120@12email.com',
                'sisi_id' => '21F1NUM1124',
                'firstname' => 'Оюутан14',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110014',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student15120@12email.com',
                'sisi_id' => '21F1NUM1015',
                'firstname' => 'Оюутан15',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110015',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student16120@12email.com',
                'sisi_id' => '21F1NUM1016',
                'firstname' => 'Оюутан16',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110016',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student17120@12email.com',
                'sisi_id' => '21F1NUM1017',
                'firstname' => 'Оюутан17',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110017',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student18120@12email.com',
                'sisi_id' => '21F1NUM1018',
                'firstname' => 'Оюутан18',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110018',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student19120@12email.com',
                'sisi_id' => '21F1NUM1019',
                'firstname' => 'Оюутан19',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110019',
                'is_choosed' => true,
                'proposed_number' => 2
          ],
            [
                'mail' => 'student20120@12email.com',
                'sisi_id' => '21F1NUM1020',
                'firstname' => 'Оюутан20',
                'lastname' => 'Сурагч',
                'program' => 'Компьютерийн Ухаан',
                'dep_id' => 1,
                'phone' => '81110020',
                'is_choosed' => true,
                'proposed_number' => 2
            ],
        ]);
    }
}
