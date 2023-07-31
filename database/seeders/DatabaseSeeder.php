<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            RoleSeeder::class,
            PaysSeeder::class,
            RegionTableSeeder::class,
            VilleSeeder::class,
            UserSeeder::class,
            CategorieSeeder::class,
            ElementSeeder::class,
            EvenementSeeder::class,
            ProduitSeeder::class,
            Kit_produitSeeder::class,
            LongSeeder::class,
            StentSeeder::class,
            Long_stentSeeder::class,
            ModeSeeder::class,
            Statut_cmdSeeder::class,
            Statut_transactionSeeder::class,
            SuiviSeeder::class
        ]);
    }
}
