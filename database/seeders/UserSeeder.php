<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /******************
        User :Client 1
         ***************************/
        $objUser = new User();
        $objUser->name = 'Client1';
        $objUser->surname = 'mama';
        $objUser->email = 'client1@domain.cm';
        $objUser->phone = '+237697847117';
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 3;
        $objUser->ville_id = 3;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: Client2");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }

        /******************
        User :Client 2
         ***************************/
        $objUser = new User();
        $objUser->name = 'Client2';
        $objUser->surname = 'mimi';
        $objUser->email = 'client2@domain.cm';
        $objUser->phone = '+237697812272';//Rufus
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 3;
        $objUser->ville_id = 3;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: Client2");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }

        $objUser = new User();
        $objUser->name = 'Administrateur';
        $objUser->surname = 'Administrateur';
        $objUser->email = 'admin@domain.cm';
        $objUser->phone = '+237690296578';//Rufus
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 1;
        $objUser->ville_id = 1;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: Admin");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }

        $objUser = new User();
        $objUser->name = 'Gestionnaire1';
        $objUser->surname = 'meme';
        $objUser->email = 'gestionnaire1@domain.cm';
        $objUser->phone = '+237652569825';//Rufus
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 2;
        $objUser->ville_id = 2;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: Gestionnaire");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }

        $objUser = new User();
        $objUser->name = 'Gestionnaire2';
        $objUser->surname = 'momo';
        $objUser->email = 'gestionnaire2@domain.cm';
        $objUser->phone = '+237652561825';//Rufus
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 2;
        $objUser->ville_id = 2;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: Gestionnaire");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }

        $objUser = new User();
        $objUser->name = 'livreur1';
        $objUser->surname = 'livreur1';
        $objUser->email = 'livreur1@domain.cm';
        $objUser->phone = '+237652562325';//Rufus
        $objUser->password = Hash::make('12345678');
        $objUser->role_id = 4;
        $objUser->ville_id = 2;
        $objUser->published   = 1;
        $objUser->generateReference();
        $objUser->generateAlias($objUser->name);
        if(!$objUser->save())
        {
            $this->command->info("Fail Seeded user: livreur1");
        }else{
            $this->command->info("Seeded user: ". $objUser->name);
        }
    }
}
