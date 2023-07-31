<?php

namespace Database\Seeders;

use App\Models\Element;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objElement=new Element();//1
        $objElement->name='Enticos SR';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//2
        $objElement->name='Sondes';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//3
        $objElement->name='Introducteur';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//4
        $objElement->name='Enticos DR';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//5
        $objElement->name='Sonde auriculaire';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//6
        $objElement->name='Sonde ventriculaire';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//7
        $objElement->name='Introducteur auriculaire';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//8
        $objElement->name='Introducteur ventriculaire';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//9
        $objElement->name='Rivacor 3 VR';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();

        $objElement=new Element();//10
        $objElement->name='Plexa S65';
        $objElement->published = 1;
        $objElement->generateReference();
        $objElement->generateAlias($objElement->name);
        $objElement->save();
    }
}
