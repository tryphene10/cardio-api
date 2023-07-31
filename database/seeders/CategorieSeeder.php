<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var  $objCategorie
         *liste des categories
         *
         */
        $objCategorie=new Categorie();//1
        $objCategorie->name='Accessoires et autres produits';
        $objCategorie->published = 1;
        $objCategorie->generateReference();
        $objCategorie->generateAlias($objCategorie->name);
        $objCategorie->save();

        $objCategorie=new Categorie();//2
        $objCategorie->name='kits';
        $objCategorie->published = 1;
        $objCategorie->generateReference();
        $objCategorie->generateAlias($objCategorie->name);
        $objCategorie->save();
    }
}
