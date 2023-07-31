<?php

namespace Database\Seeders;

use App\Models\Produit;
use App\Models\Produit_img;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Seeder Produit Simple
        $objProduit = new Produit();
        $objProduit->designation = 'Enticos 4 SR';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'Stimulateur simple chambre';
        $objProduit->prix_produit = '557324';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Enticos_4_SR.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Enticos 4 DR';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Stimulateur double chambre qui assure une synchronisation auriculo-ventriculaire si elle est nécessaire, mais sa supériorité sur la survie n’est pas démontrée. De plus, elle est associée à davantage de complications péri-opératoires (déplacements de sonde, infections de la loge) et à une augmentation du risque d’hospitalisation pour insuffisance cardiaque en cas de dysfonction sinusale.';
        $objProduit->prix_produit = '622891';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Enticos_4_DR.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Rivacor 3 VR‐T DF4 ProMRI';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Améliorer le traitement et réduire le risque jusqu\'à 15 ans[1] Pour les patients souffrant d\'arythmies, le traitement par DAI peut être un filet de sécurité essentiel, où il est vital de minimiser les risques et d\'améliorer la qualité de vie à long terme. C\'est ce que les tout nouveaux systèmes de DAI Rivacor 3, plus petits et plus simples, sont conçus pour faire jusqu\'à 15 ans[9] - en optimisant le traitement quand et où cela est important. Lorsqu\'ils sont utilisés avec la technologie de surveillance à domicile, la FA peut être détectée plus tôt[14] et les chocs inappropriés ainsi que les taux d\'hospitalisation peuvent être réduits[19]. Et cela a été cliniquement prouvé.';
        $objProduit->prix_produit = '3602500';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/3vr.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Orsiro Stent Coronaire Actif Hybride';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Orsiro permet de réduire encore plus le risque de thrombose (caillot de sang qui se forme dans une artère ou une veine) grâce à une fine couche de silicium. IL possède un nouveau stent appelé stent active hybride dont la technologie de celui-ci minimise également les réactions inflammatoires liées à la présence d’un corps étranger.';
        $objProduit->prix_produit = '256000';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/orsiro_stent_coronaire_actif.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Solia T 53';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'BIOTRONIK ProMRI1 permet aux patients de passer des examens IRM sous certaines conditions Design hélicoïdal des conducteurs dans la partie intracardiaque du corps de la sonde, afin de préserver les conducteurs des contraintes mécaniques';
        $objProduit->prix_produit = '229486';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/13.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Solia S 53';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Sonde auriculaire à vis. Connexion : IS-1 Fixation : Vis rétractable électriquement active Longueur : 53 cm Numéro de référence : 377177';
        $objProduit->prix_produit = '229486';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/14.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'Solia S 60';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'Sonde ventriculaire à vis. Connexion : IS-1 Fixation : Vis rétractable électriquement active Longueur : 60 cm Numéro de référence : 377179';
        $objProduit->prix_produit = '229486';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Solia_S_6.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'solia-T60';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'Stimulateur double chambre qui assure une synchronisation auriculo-ventriculaire si elle est nécessaire, mais sa supériorité sur la survie n’est pas démontrée. De plus, elle est associée à davantage de complications péri-opératoires (déplacements de sonde, infections de la loge) et à une augmentation du risque d’hospitalisation pour insuffisance cardiaque en cas de dysfonction sinusale.';
        $objProduit->prix_produit = '229486';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/14.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = 'LI‐8 plus G';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'introducteur 8F';
        $objProduit->prix_produit = '16391';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/introducteur-8f.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = 'LI‐7 plus G';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'Gamme introducteur 7F';
        $objProduit->prix_produit = '16391';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/introducteur-7f.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = 'LI‐9 plus G';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = 15;
        $objProduit->description = 'Gamme introducteur 9F';
        $objProduit->prix_produit = '16391';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/introducteur-9f.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = 'Reocor S';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'un stimulateur cardiaque externe à chambre unique, alimenté par une batterie, destiné à une utilisation en clinique. Le stimulateur est connecté à des sondes de stimulation cardiaque temporaires (y compris les fils cardiaques myocardiques et les cathéters implantables transveineux). La connexion se fait directement ou via un câble patient séparé et un adaptateur, si nécessaire.';
        $objProduit->prix_produit = '1639186';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/reocorS.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = '5F Bipolar Pacing Catheter';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Les cathéters de stimulation bipolaires 5F avec et sans ballon sont votre choix lorsqu\'une stimulation de base est requise dans une situation d\'urgence. Ces produits permettent une stimulation bipolaire sûre, sécurisée et précise pour prendre en charge les procédures critiques.';
        $objProduit->prix_produit = '52454';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/5F_Bipolar_Pacing_Catheter.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }

        $objProduit = new Produit();
        $objProduit->designation = 'Plexa ProMRI SD 65/16';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 1;
        //$objProduit->kit_id = '';
        $objProduit->description = 'BIOTRONIK ProMRI1 permet aux patients de passer des examens IRM sous certaines conditions Design hélicoïdal des conducteurs dans la partie intracardiaque du corps de la sonde, afin de préserver les conducteurs des contraintes mécaniques';
        $objProduit->prix_produit = '1049080';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Plexa_S65.jpg';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }
        }


        //Produit kits

        $objProduit = new Produit();
        $objProduit->designation = 'kit Pacemaker simple chambre';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 2;
        //$objProduit->kit_id = '';
        //$objProduit->description = 'Stimulateur simple chambre';
        //$objProduit->prix_produit = '557.324';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Enticos_4_SR-3.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'kit Pacemaker double chambre';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 2;
        //$objProduit->kit_id = '';
        //$objProduit->description = 'Stimulateur simple chambre';
        //$objProduit->prix_produit = '557.324';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/Enticos_4_DR-3.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }

        $objProduit = new Produit();
        $objProduit->designation = 'kit Defibrilateur simple chambre';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 2;
        //$objProduit->kit_id = '';
        //$objProduit->description = 'Stimulateur simple chambre';
        //$objProduit->prix_produit = '557.324';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        if($objProduit->save()){
            $objImage = new Produit_img();
            $objImage->name = 'cardio-afrique/public/img/product/16-1.png';
            $objImage->produit_id = $objProduit->id;
            $objImage->published = 1;
            $objImage->generateReference();
            $objImage->generateAlias($objImage->name);
            $objImage->produit()->associate($objProduit);
            if(!$objImage->save())
            {
                $this->command->info("Fail Seeded Image:igname");
            }else{
                $this->command->info("Seeded Image: ". $objImage->name);
            }

        }


       /* $objProduit = new Produit();
        $objProduit->designation = 'Enticos 4 SR';
        $objProduit->user_id = 4;
        $objProduit->categorie_id = 2;
        //$objProduit->kit_id = '';
        $objProduit->description = 'Stimulateur simple chambre';
        $objProduit->prix_produit = '557.324';
        $objProduit->qte = '1200';
        $objProduit->published = 1;
        $objProduit->generateReference();
        $objProduit->generateAlias($objProduit->designation);
        $objProduit->save();*/
    }
}
