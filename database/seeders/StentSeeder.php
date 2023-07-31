<?php

namespace Database\Seeders;

use App\Models\Stent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stent = new Stent();
        $stent->valeur = 2.25;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();

        $stent = new Stent();
        $stent->valeur = 2.50;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();

        $stent = new Stent();
        $stent->valeur = 2.75;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();

        $stent = new Stent();
        $stent->valeur = 3.00;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();

        $stent = new Stent();
        $stent->valeur = 3.50;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();

        $stent = new Stent();
        $stent->valeur = 4.00;
        $stent->published = 1;
        $stent->generateReference();
        $stent->generateAlias($stent->valeur);
        $stent->save();
    }
}
