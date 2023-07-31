<?php

namespace Database\Seeders;

use App\Models\Mode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objMode=new Mode();//1
        $objMode->name='cash';
        $objMode->published = 1;
        $objMode->generateReference();
        $objMode->generateAlias($objMode->name);
        $objMode->save();

        $objMode=new Mode();//1
        $objMode->name='virement';
        $objMode->published = 1;
        $objMode->generateReference();
        $objMode->generateAlias($objMode->name);
        $objMode->save();

        $objMode=new Mode();//1
        $objMode->name='transfert';
        $objMode->published = 1;
        $objMode->generateReference();
        $objMode->generateAlias($objMode->name);
        $objMode->save();

        $objMode=new Mode();//1
        $objMode->name='orange';
        $objMode->logo='cardio-afrique/public/img/logo-mode-paiement/orangeMoney.png';
        $objMode->published = 1;
        $objMode->generateReference();
        $objMode->generateAlias($objMode->name);
        $objMode->save();

        $objMode=new Mode();//1
        $objMode->name='mtn';
        $objMode->logo='cardio-afrique/public/img/logo-mode-paiement/mtnMoney.png';
        $objMode->published = 1;
        $objMode->generateReference();
        $objMode->generateAlias($objMode->name);
        $objMode->save();
    }
}
