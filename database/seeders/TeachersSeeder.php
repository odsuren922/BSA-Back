<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('teachers')->insert([
            
            [
                  'mail'=> 'supervisor1@example.com',
                  "firstname"=> "Баатарбилэг",
                  "lastname"=> "А",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor2@example.com",
                  "firstname"=> "Мөнххул",
                  "lastname"=> "А",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor3@example.com",
                  "firstname"=> "Аззаяа",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Дадлагажигч багш"
            ],
            [
                  "mail"=> "supervisor4@example.com",
                  "firstname"=> "Батчимэг",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor5@example.com",
                  "firstname"=> "Амгалан",
                  "lastname"=> "А",
                  "dep_id"=> "1",
                  "superior"=> "Дадлагажигч багш"
            ],
            [
                  "mail"=> "supervisor6@example.com",
                  "firstname"=> "Буянхишиг",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Лаборант"
            ],
            [
                  "mail"=> "supervisor7@example.com",
                  "firstname"=> "Буянхишиг",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor8@example.com",
                  "firstname"=> "Доржнамжмаа",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor9@example.com",
                  "firstname"=> "Маралсүрэн",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor10@example.com",
                  "firstname"=> "Наранбат",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor11@example.com",
                  "firstname"=> "Сувдаа",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor12@example.com",
                  "firstname"=> "Хулан",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor13@example.com",
                  "firstname"=> "Хурцбилэг",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor14@example.com",
                  "firstname"=> "Энхтуяа",
                  "lastname"=> "Б",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor15@example.com",
                  "firstname"=> "Батболд",
                  "lastname"=> "Г",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor16@example.com",
                  "firstname"=> "Батцэрэн",
                  "lastname"=> "Г",
                  "dep_id"=> "1",
                  "superior"=> "Лаборант"
            ],
            [
                  "mail"=> "supervisor17@example.com",
                  "firstname"=> "Гантугчаа",
                  "lastname"=> "Г",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor18@example.com",
                  "firstname"=> "Гайхнаа",
                  "lastname"=> "Г",
                  "dep_id"=> "1",
                  "superior"=> "Лаборант"
            ],
            [
                  "mail"=> "supervisor19@example.com",
                  "firstname"=> "Ганмаа",
                  "lastname"=> "Д",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor20@example.com",
                  "firstname"=> "Нандинцэцэг",
                  "lastname"=> "Д",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor21@example.com",
                  "firstname"=> "Энхзул",
                  "lastname"=> "Д",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor22@example.com",
                  "firstname"=> "Жигжидсүрэн",
                  "lastname"=> "Ж",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor23@example.com",
                  "firstname"=> "Энхчимэг",
                  "lastname"=> "Л",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor24@example.com",
                  "firstname"=> "Билгүүнсаруул",
                  "lastname"=> "М",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor25@example.com",
                  "firstname"=> "Бямбасүрэн",
                  "lastname"=> "Н",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor26@example.com",
                  "firstname"=> "Есөнбаяр",
                  "lastname"=> "Н",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor27@example.com",
                  "firstname"=> "Лхагвасүрэн",
                  "lastname"=> "Н",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor28@example.com",
                  "firstname"=> "Мөнх-Эрдэнэ",
                  "lastname"=> "О",
                  "dep_id"=> "1",
                  "superior"=> "Профессор"
            ],
            [
                  "mail"=> "supervisor29@example.com",
                  "firstname"=> "Анхтуяа",
                  "lastname"=> "О",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor30@example.com",
                  "firstname"=> "Билгүүн",
                  "lastname"=> "О",
                  "dep_id"=> "1",
                  "superior"=> "Дадлагажигч багш"
            ],
            [
                  "mail"=> "supervisor31@example.com",
                  "firstname"=> "Оюуннаран",
                  "lastname"=> "О",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor32@example.com",
                  "firstname"=> "Баярбат",
                  "lastname"=> "О",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor33@example.com",
                  "firstname"=> "Наранбаяр",
                  "lastname"=> "П",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor34@example.com",
                  "firstname"=> "Тулгаа",
                  "lastname"=> "П",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor35@example.com",
                  "firstname"=> "Жавхлан",
                  "lastname"=> "Р",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor36@example.com",
                  "firstname"=> "Батбаяр",
                  "lastname"=> "С",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor37@example.com",
                  "firstname"=> "Нямхүү",
                  "lastname"=> "С",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor38@example.com",
                  "firstname"=> "Түмэндэмбэрэл",
                  "lastname"=> "С",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor39@example.com",
                  "firstname"=> "Уянга",
                  "lastname"=> "С",
                  "dep_id"=> "1",
                  "superior"=> "Профессор"
            ],
            [
                  "mail"=> "supervisor40@example.com",
                  "firstname"=> "Мөнх-Орших",
                  "lastname"=> "Т",
                  "dep_id"=> "1",
                  "superior"=> "Лаборант"
            ],
            [
                  "mail"=> "supervisor41@example.com",
                  "firstname"=> "Цэвээнсүрэн",
                  "lastname"=> "Т",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor42@example.com",
                  "firstname"=> "Оюундолгор",
                  "lastname"=> "Х",
                  "dep_id"=> "1",
                  "superior"=> "Профессор"
            ],
            [
                  "mail"=> "supervisor43@example.com",
                  "firstname"=> "Цэдэнсодном",
                  "lastname"=> "Ц",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor44@example.com",
                  "firstname"=> "Хүрэл-Очир",
                  "lastname"=> "Ц",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor45@example.com",
                  "firstname"=> "Алтангэрэл",
                  "lastname"=> "Ч",
                  "dep_id"=> "1",
                  "superior"=> "Дэд профессор"
            ],
            [
                  "mail"=> "supervisor46@example.com",
                  "firstname"=> "Алтансүх",
                  "lastname"=> "Ч",
                  "dep_id"=> "1",
                  "superior"=> "Тэнхимийн эрхлэгч"
            ],
            [
                  "mail"=> "supervisor47@example.com",
                  "firstname"=> "Мягмар-Эрдэнэ",
                  "lastname"=> "Ч",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor48@example.com",
                  "firstname"=> "Бат-Өлзий",
                  "lastname"=> "Ш",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor49@example.com",
                  "firstname"=> "Амармэнд",
                  "lastname"=> "Э",
                  "dep_id"=> "1",
                  "superior"=> "Багш"
            ],
            [
                  "mail"=> "supervisor50@example.com",
                  "firstname"=> "Билгүүн",
                  "lastname"=> "Э",
                  "dep_id"=> "1",
                  "superior"=> "Лаборант"
            ],
            [
                  "mail"=> "supervisor51@example.com",
                  "firstname"=> "Саранцэцэг",
                  "lastname"=> "Э",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ],
            [
                  "mail"=> "supervisor52@example.com",
                  "firstname"=> "Цэцэгдэлгэр",
                  "lastname"=> "Ц",
                  "dep_id"=> "1",
                  "superior"=> "Ахлах багш"
            ],
            [
                  "mail"=> "supervisor53@example.com",
                  "firstname"=> "Анхбаяр",
                  "lastname"=> "Ю",
                  "dep_id"=> "1",
                  "superior"=> "Цагийн багш"
            ]
          
      ]);
    }
}
