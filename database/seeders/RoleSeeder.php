<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objRole = new Role();
        $objRole->name = 'Administrateur';
        $objRole->published   = 1;
        $objRole->generateReference();
        $objRole->generateAlias("admin");
        if(!$objRole->save())
        {
            $this->command->info("Fail Seeded Role: Administrateur");
        }else{
            $this->command->info("Seeded Role: ". $objRole->name);
        }

        //
        $objRole = new Role();
        $objRole->name = 'Gestionnaire';
        $objRole->published   = 1;
        $objRole->generateReference();
        $objRole->generateAlias($objRole->name);
        if(!$objRole->save())
        {
            $this->command->info("Fail Seeded Role: Gestionnaire");
        }else{
            $this->command->info("Seeded Role: ". $objRole->name);
        }

        //
        $objRole = new Role();
        $objRole->name = 'Client';
        $objRole->published   = 1;
        $objRole->generateReference();
        $objRole->generateAlias($objRole->name);
        if(!$objRole->save())
        {
            $this->command->info("Fail Seeded Role: Client");
        }else{
            $this->command->info("Seeded Role: ". $objRole->name);
        }

        //
        $objRole = new Role();
        $objRole->name = 'Livreur';
        $objRole->published   = 1;
        $objRole->generateReference();
        $objRole->generateAlias($objRole->name);
        if(!$objRole->save())
        {
            $this->command->info("Fail Seeded Role: Livreur");
        }else{
            $this->command->info("Seeded Role: ". $objRole->name);
        }
    }
}
