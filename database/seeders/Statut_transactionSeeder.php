<?php

namespace Database\Seeders;

use App\Models\Statut_transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Statut_transactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objStatutTrans=new Statut_transaction();//1
        $objStatutTrans->name='initie';
        $objStatutTrans->published = 1;
        $objStatutTrans->generateReference();
        $objStatutTrans->generateAlias($objStatutTrans->name);
        $objStatutTrans->save();

        $objStatutTrans=new Statut_transaction();//1
        $objStatutTrans->name='echouer';
        $objStatutTrans->published = 1;
        $objStatutTrans->generateReference();
        $objStatutTrans->generateAlias($objStatutTrans->name);
        $objStatutTrans->save();

        $objStatutTrans=new Statut_transaction();//1
        $objStatutTrans->name='reussie';
        $objStatutTrans->published = 1;
        $objStatutTrans->generateReference();
        $objStatutTrans->generateAlias($objStatutTrans->name);
        $objStatutTrans->save();
    }
}
