<?php

namespace Database\Seeders;

use App\Models\Statut_cmd;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Statut_cmdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objStatutCmd=new Statut_cmd();//1
        $objStatutCmd->name='paiement en attente';
        $objStatutCmd->published = 1;
        $objStatutCmd->generateReference();
        $objStatutCmd->generateAlias($objStatutCmd->name);
        $objStatutCmd->save();

        $objStatutCmd=new Statut_cmd();//2
        $objStatutCmd->name='paiement partiel';
        $objStatutCmd->published = 1;
        $objStatutCmd->generateReference();
        $objStatutCmd->generateAlias($objStatutCmd->name);
        $objStatutCmd->save();

        $objStatutCmd=new Statut_cmd();//3
        $objStatutCmd->name='paiement echoué';
        $objStatutCmd->published = 1;
        $objStatutCmd->generateReference();
        $objStatutCmd->generateAlias($objStatutCmd->name);
        $objStatutCmd->save();

        $objStatutCmd=new Statut_cmd();//4
        $objStatutCmd->name='paiement terminé';
        $objStatutCmd->published = 1;
        $objStatutCmd->generateReference();
        $objStatutCmd->generateAlias($objStatutCmd->name);
        $objStatutCmd->save();
    }
}
