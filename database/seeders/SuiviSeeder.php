<?php

namespace Database\Seeders;

use App\Models\Suivi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuiviSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objSuivi=new Suivi();//1
        $objSuivi->name='collecte';
        $objSuivi->published = 1;
        $objSuivi->generateReference();
        $objSuivi->generateAlias($objSuivi->name);
        $objSuivi->save();

        $objSuivi=new Suivi();//1
        $objSuivi->name='transport';
        $objSuivi->published = 1;
        $objSuivi->generateReference();
        $objSuivi->generateAlias($objSuivi->name);
        $objSuivi->save();

        $objSuivi=new Suivi();//1
        $objSuivi->name='livré';
        $objSuivi->published = 1;
        $objSuivi->generateReference();
        $objSuivi->generateAlias($objSuivi->name);
        $objSuivi->save();
    }
}
