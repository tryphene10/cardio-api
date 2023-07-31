<?php

namespace Database\Seeders;

use App\Models\Evenement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EvenementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objEvent=new Evenement();//1
        $objEvent->titre='The Most Inspiring EHRA Congress 2021';
        $objEvent->user_id = 4;
        $objEvent->begin = '2022-03-30 12:00:00';
        $objEvent->end = '2022-03-30 12:30:00';
        $objEvent->url_image='cardio-afrique/public/img/service/32.jpg';
        $objEvent->description='The Most Inspiring EHRA Congress 2021';
        $objEvent->published = 1;
        $objEvent->lieu_evenement = 'Rue marché new deido';
        $objEvent->generateReference();
        $objEvent->generateAlias($objEvent->name);
        $objEvent->save();

        $objEvent=new Evenement();//1
        $objEvent->titre='10 Brilliant Ways To Decorate Your Home';
        $objEvent->user_id = 4;
        $objEvent->begin = '2022-03-30 08:00:00';
        $objEvent->end = '2024-01-01 17:00:00';
        $objEvent->url_image='cardio-afrique/public/img/blog/5.jpg';
        $objEvent->description='10 Brilliant Ways To Decorate Your Home';
        $objEvent->lieu_evenement = 'Rue marché new deido';
        $objEvent->published = 1;
        $objEvent->generateReference();
        $objEvent->generateAlias($objEvent->name);
        $objEvent->save();

        $objEvent=new Evenement();//1
        $objEvent->titre='The Most Inspiring Interior Design Of 2021';
        $objEvent->user_id = 4;
        $objEvent->begin = '2023-01-01 08:00:00';
        $objEvent->end = '2023-06-01 18:00:00';
        $objEvent->url_image='cardio-afrique/public/img/img-slide/11.jpg';
        $objEvent->description='The Most Inspiring Interior Design Of 2021';
        $objEvent->lieu_evenement = 'Rue marché new deido';
        $objEvent->published = 1;
        $objEvent->generateReference();
        $objEvent->generateAlias($objEvent->name);
        $objEvent->save();
    }
}
