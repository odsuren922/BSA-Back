<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
        //
        DB::table('teachers')->insert([
            
              [
                    'mail'=> 'supervisor1@example.com',
                    "firstname"=> "Баатарбилэг",
                    "lastname"=> "А",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor2@example.com",
                    "firstname"=> "Мөнххул",
                    "lastname"=> "А",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor3@example.com",
                    "firstname"=> "Аззаяа",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Дадлагажигч багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor4@example.com",
                    "firstname"=> "Батчимэг",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Ахлах багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor5@example.com",
                    "firstname"=> "Амгалан",
                    "lastname"=> "А",
                    "dep_id"=> "1",
                    "superior"=> "Дадлагажигч багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor6@example.com",
                    "firstname"=> "Буянхишиг",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Лаборант",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor7@example.com",
                    "firstname"=> "Буянхишиг",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Цагийн багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor8@example.com",
                    "firstname"=> "Доржнамжмаа",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor9@example.com",
                    "firstname"=> "Маралсүрэн",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Ахлах багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor10@example.com",
                    "firstname"=> "Наранбат",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor11@example.com",
                    "firstname"=> "Сувдаа",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Дэд профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor12@example.com",
                    "firstname"=> "Хулан",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Дэд профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor13@example.com",
                    "firstname"=> "Хурцбилэг",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Цагийн багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor14@example.com",
                    "firstname"=> "Энхтуяа",
                    "lastname"=> "Б",
                    "dep_id"=> "1",
                    "superior"=> "Дэд профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor15@example.com",
                    "firstname"=> "Батболд",
                    "lastname"=> "Г",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor16@example.com",
                    "firstname"=> "Батцэрэн",
                    "lastname"=> "Г",
                    "dep_id"=> "1",
                    "superior"=> "Лаборант",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor17@example.com",
                    "firstname"=> "Гантугчаа",
                    "lastname"=> "Г",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              
              [
                    "mail"=> "supervisor37@example.com",
                    "firstname"=> "Нямхүү",
                    "lastname"=> "С",
                    "dep_id"=> "1",
                    "superior"=> "Дэд профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor38@example.com",
                    "firstname"=> "Түмэндэмбэрэл",
                    "lastname"=> "С",
                    "dep_id"=> "1",
                    "superior"=> "Дэд профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor39@example.com",
                    "firstname"=> "Уянга",
                    "lastname"=> "С",
                    "dep_id"=> "1",
                    "superior"=> "Профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor40@example.com",
                    "firstname"=> "Мөнх-Орших",
                    "lastname"=> "Т",
                    "dep_id"=> "1",
                    "superior"=> "Лаборант",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor41@example.com",
                    "firstname"=> "Цэвээнсүрэн",
                    "lastname"=> "Т",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor42@example.com",
                    "firstname"=> "Оюундолгор",
                    "lastname"=> "Х",
                    "dep_id"=> "1",
                    "superior"=> "Профессор",
                    "numof_choosed_stud" =>"0"
              ],
              [
                    "mail"=> "supervisor43@example.com",
                    "firstname"=> "Цэдэнсодном",
                    "lastname"=> "Ц",
                    "dep_id"=> "1",
                    "superior"=> "Багш",
                    "numof_choosed_stud" =>"0"
              ],
            
            
        ]);
    }
}