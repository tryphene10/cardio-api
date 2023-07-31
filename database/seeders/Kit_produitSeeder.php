<?php

namespace Database\Seeders;

use App\Models\Kit_produit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Kit_produitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 1;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 1;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 7;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 2;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 8;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 2;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 10;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 3;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 9;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 3;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 11;
        $objKitProduit->kit_id = 15;
        $objKitProduit->element_id = 3;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 2;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 4;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        //Sonde auriculaire:

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 6;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 5;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 5;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 5;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        //Sonde ventriculaire:

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 7;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 6;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 8;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 6;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        //Introducteur auriculaire

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 10;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 7;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 9;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 7;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 11;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 7;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        //Introducteur ventriculaire

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 10;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 8;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 9;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 8;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 11;
        $objKitProduit->kit_id = 16;
        $objKitProduit->element_id = 8;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        //Introducteur ventriculaire

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 3;
        $objKitProduit->kit_id = 17;
        $objKitProduit->element_id = 9;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

        $objKitProduit = new Kit_produit();
        $objKitProduit->produit_id = 14;
        $objKitProduit->kit_id = 17;
        $objKitProduit->element_id = 10;
        $objKitProduit->published = 1;
        $objKitProduit->generateReference();
        $objKitProduit->generateAlias('kit_produit');
        $objKitProduit->save();

    }
}
