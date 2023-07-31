<?php

namespace Database\Seeders;

use App\Models\Long;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $long = new Long();
        $long->valeur = 9;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 13;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 15;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 18;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 22;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 26;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 30;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 35;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();

        $long = new Long();
        $long->valeur = 40;
        $long->published = 1;
        $long->generateReference();
        $long->generateAlias($long->valeur);
        $long->save();
    }
}
