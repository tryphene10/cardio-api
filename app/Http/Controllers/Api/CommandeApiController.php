<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderMailToCustomer;
use App\Mail\orderMailToGestionnaire;
use App\Mail\OrderOsiroToAdmin;
use App\Mail\OrderOsiroToCustomer;
use App\Mail\OrderOsiroToGestionnaire;
use App\Mail\SendDeliverymanMail;
use App\Mail\SendOrderMessageToAdmin;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Commande;
use App\Models\Detail_panier;
use App\Models\Long;
use App\Models\Long_stent;
use App\Models\Transaction;
use App\Models\Statut_transaction;
use App\Models\Panier;
use App\Models\Ville;
use App\Models\Statut_cmd;
use App\Models\Produit;
use App\Models\Mode;
use App\Models\Stent;
use App\Models\Suivi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Console\Input\Input as InputInput;
use Illuminate\Support\Facades\Storage;

use Mailjet\LaravelMailjet\Facades\Mailjet;


class CommandeApiController extends Controller
{

    public function create(Request $request)
    {
        $this->_fnErrorCode = 1;

        //On vérifie que la commande est bien envoyé !
        $objListCommande = collect(json_decode($request->getContent(), true));
        if (empty($objListCommande)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste des produits de la commande est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 3;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        //On vérifie le rôle client
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != "client") {
			$this->_errorCode = 4;
			$this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        DB::beginTransaction();

        $objCommande = (object)[];
        $listGestionnaire = array();
        $mail_reponse = "";


        //--------------------------------------------------------------------
        // CURL
        //--------------------------------------------------------------------

        if($objListCommande->has("panier")) {

            if($objListCommande->has("ville")) {

                $objVille = Ville::where('id', '=', intval($objListCommande["ville"]))->first();
                if(empty($objVille)) {
                    DB::rollback();
                    $this->_errorCode = 5;
                    $this->_response['message'][] = "La ville n'existe pas!";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                if ($objListCommande->has("lieu_livraison")) {

                    //-----------------------------------------------------------------------------------------------
                    //Statut waiting
                    //-----------------------------------------------------------------------------------------------
                    $objStatutCmde = Statut_cmd::where('name', '=', 'paiement en attente')->first();
                    if (empty($objStatutCmde)) {
                        DB::rollback();
                        $this->_errorCode = 6;
                        $this->_response['message'][] = "Le statut de la commande n'existe pas!";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                    //-----------------------------------------------------------------------------------------------
                    //Création de la commande
                    //-----------------------------------------------------------------------------------------------
                    try {

                        $objCommande = new Commande();
                        $objCommande->published = 1;
                        $objCommande->lieu_livraison = $objListCommande["lieu_livraison"];
                        $objCommande->generateReference();
                        $objCommande->generateAlias("Commande".$objCommande->id);
                        $objCommande->user_client()->associate($objUser);
                        $objCommande->statut_cmd()->associate($objStatutCmde);
                        $objCommande->ville()->associate($objVille);
                        $objCommande->save();

                    } catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 7;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                    $colProduct = collect();
                    $montantCmde = 0;
                    $prixUProduit = 0;
                    foreach($objListCommande['panier'] as $panier) {

                        if($panier['produit'] != "") {
                            //Récupération de l'objet produit
                            $objProduit = Produit::where('ref', '=', $panier['produit'])->first();
                            $prixUProduit = $objProduit->prix_produit;
                            if($panier['quantite'] != "") {

                                if($panier['prix_total'] != "") {

                                    try{

                                        $objPanier = new Panier();
                                        $objPanier->quantite = $panier['quantite'];
                                        $objPanier->prix_total = $panier['prix_total'];
                                        $objPanier->published = 1;
                                        $objPanier->generateReference();
                                        $objPanier->generateAlias("Commande".$objCommande->id);
                                        $objPanier->commande()->associate($objCommande);
                                        $objPanier->produit()->associate($objProduit);
                                        $objPanier->save();

                                    }catch(Exception $objException) {
                                        DB::rollback();
                                        $this->_errorCode = 8;
                                        if (in_array($this->_env, ['local', 'development'])) {
                                        }
                                        $this->_response['message'] = $objException->getMessage();
                                        $this->_response['error_code'] = $this->prepareErrorCode();
                                        return response()->json($this->_response);
                                    }

                                    $montantCmde = $objPanier->prix_total + $montantCmde;
                                    
                                    if(isset($panier['detail_produit'])) {

                                        foreach($panier['detail_produit'] as $detail) {

                                            if($detail['prix_total'] != "") {

                                                /*if(isset($detail['long']) && isset($detail['stent']) && !isset($detail['sous_produit'])) {

                                                    $objLong = Long::where('ref', '=', $detail['long'])->first();

                                                    $objLong = Stent::where('ref', '=', $detail['stent'])->first();

                                                    $objLongStent = Long_stent::where('long_id', '=', $objLong->id)->where('stent_id', '=', $objLong->id)->first();

                                                    try{

                                                        $objDetail_panier = new Detail_panier();
                                                        if(isset($detail['quantite'])) {
                                                            $objDetail_panier->quantite = $detail['quantite'];
                                                        }
                                                        $objDetail_panier->prix_total = $detail['prix_total'];
                                                        $objDetail_panier->published = 1;
                                                        $objDetail_panier->generateReference();
                                                        $objDetail_panier->generateAlias("Panier".$objPanier->id);
                                                        $objDetail_panier->panier()->associate($objPanier);
                                                        $objDetail_panier->long_stent()->associate($objLongStent);
                                                        $objDetail_panier->save();

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        $this->_errorCode = 9;
                                                        if (in_array($this->_env, ['local', 'development'])) {
                                                        }
                                                        $this->_response['message'] = $objException->getMessage();
                                                        $this->_response['error_code'] = $this->prepareErrorCode();
                                                        return response()->json($this->_response);
                                                    }
                                                }*/

                                                if(isset($detail['sous_produit'])) {

                                                    $objSubProduit = Produit::where('ref', '=', $detail['sous_produit'])->first();

                                                    try{

                                                        $objDetail_panier = new Detail_panier();
                                                        if(isset($detail['quantite'])) {
                                                            $objDetail_panier->quantite = $detail['quantite'];
                                                        }
                                                        $objDetail_panier->prix_total = $detail['prix_total'];
                                                        $objDetail_panier->published = 1;
                                                        $objDetail_panier->generateReference();
                                                        $objDetail_panier->generateAlias("Panier".$objPanier->id);
                                                        $objDetail_panier->panier()->associate($objPanier);
                                                        $objDetail_panier->produit()->associate($objSubProduit);
                                                        $objDetail_panier->save();

                                                    }catch(Exception $objException) {
                                                        DB::rollback();
                                                        $this->_errorCode = 9;
                                                        if (in_array($this->_env, ['local', 'development'])) {
                                                        }
                                                        $this->_response['message'] = $objException->getMessage();
                                                        $this->_response['error_code'] = $this->prepareErrorCode();
                                                        return response()->json($this->_response);
                                                    }

                                                    $prixUProduit = intval($objSubProduit->prix_produit) + $prixUProduit;
                                                }
                                            }else {
                                                DB::rollback();
                                                $this->_errorCode = 10;
                                                $this->_response['message'][] = "Le prix_total n'existe pas!";
                                                $this->_response['error_code'] = $this->prepareErrorCode();
                                                return response()->json($this->_response);
                                            }

                                        }
                                    }

                                    $colProduct->push(array(
                                        'product' => $objProduit,
                                        'product_unit_price' => $prixUProduit,
                                        'panier' => $objPanier
                                    ));

                                }else {
                                    DB::rollback();
                                    $this->_errorCode = 11;
                                    $this->_response['message'][] = "Le prix_total n'existe pas!";
                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                    return response()->json($this->_response);
                                }

                            }else {
                                DB::rollback();
                                $this->_errorCode = 12;
                                $this->_response['message'][] = "La quantité n'existe pas!";
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            $objUserGestionnaire = User::where('id','=',$objProduit->user_id)->first();
                            if(!in_array($objUserGestionnaire->email, $listGestionnaire)) {
                                $listGestionnaire[] = $objUserGestionnaire->email;
                            }

                        }else {
                            DB::rollback();
                            $this->_errorCode = 13;
                            $this->_response['message'][] = "Le produit n'existe pas!";
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                    }

                    $dataCustomer = [
                        'customer'=> $objUser,
                        'commande'=> $objCommande,
                        'products'=> $colProduct,
                        'montant_commande'=> $montantCmde
                    ];

                    /*
                    * Shurtcode d'envoi de mail au client
                     */

                    try {

                        Mail::to($objUser->email)
                        ->send(new OrderMailToCustomer($dataCustomer));

                    }catch (Exception $objException) {

                        DB::rollBack();
                        $this->_errorCode = 14;
                        if(in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }



                    /*
                    * Shurtcode d'envoi de mail au gestionnaire
                     */
                    foreach($listGestionnaire as $userGestionnaire) {
                        $objUserGestionnaire = User::where('email','=',$userGestionnaire)->first();

                        $data = [
                            'gestionnaire'=> $objUserGestionnaire,
                            'customer'=> $objUser,
                            'commande'=> $objCommande,
                            'products'=> $colProduct,
                            'montant_commande'=> $montantCmde
                        ];

                        try {

                            Mail::to($objUserGestionnaire->email)
                            ->send(new orderMailToGestionnaire($data));

                        }catch (Exception $objException) {

                            DB::rollBack();
                            $this->_errorCode = 15;
                            if(in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }
                    }


                    $objUserAdmin = User::where('role_id','=',1)->first();
                   
                    $adminData = [
                        'customer'=> $objUser,
                        'commande'=> $objCommande,
                        'products'=> $colProduct,
                        'montant_commande'=> $montantCmde
                    ];

                    /*
                    * Shurtcode d'envoi de mail à l'admin
                    */
                    try {

                        Mail::to($objUserAdmin->email)
                        ->send(new SendOrderMessageToAdmin($adminData));

                    }catch (Exception $objException) {

                        DB::rollBack();
                        $this->_errorCode = 16;
                        if(in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }


                    $mail_reponse = 'Email has been sent to gestionnaire, admin and customer';


                }else {
                    DB::rollback();
                    $this->_errorCode = 17;
                    $this->_response['message'][] = "Veuillez entrer un lieu de livraison!";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else {
                DB::rollback();
                $this->_errorCode = 18;
                $this->_response['message'][] = "Veuillez choisir une ville!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }


        }else {
            DB::rollback();
            $this->_errorCode = 19;
            $this->_response['message'][] = "Le panier est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        // Commit the queries!
        DB::commit();
        $toReturn = [
            'mail_reponse' => $mail_reponse,
            'objet' => $objCommande
        ];

        $this->_response['message'] = "Commande créée avec succès.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }


    public function createOrderOsiro(Request $request)
    {
        $this->_fnErrorCode = 1;

        //On vérifie que la commande est bien envoyé !
        $objListCommande = collect(json_decode($request->getContent(), true));
        if(empty($objListCommande)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste des produits de la commande est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 3;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        //On vérifie le rôle client
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != 'client') {
			$this->_errorCode = 4;
			$this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        DB::beginTransaction();

        $objCommande = (object)[];
        $listGestionnaire = array();
        $mail_reponse = "";

        if($objListCommande->has('ville')) {

            $objVille = Ville::where('id', '=', intval($objListCommande['ville']))->first();
            if(empty($objVille)) {
                DB::rollback();
                $this->_errorCode = 5;
                $this->_response['message'][] = "La ville n'existe pas!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            if ($objListCommande->has('lieu_livraison')) {

                if($objListCommande->has('produit')) {

                    if($objListCommande->has('quantite')) {

                        if($objListCommande->has('prix_total')) {

                            //Récupération de l'objet produit
                            $objProduit = Produit::where('ref', '=', $objListCommande['produit'])->first();
                            if(empty($objProduit)) {
                                DB::rollback();
                                $this->_errorCode = 6;
                                $this->_response['message'][] = "Le produit n'existe pas!";
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Statut waiting
                            //-----------------------------------------------------------------------------------------------
                            $objStatutCmde = Statut_cmd::where('name', '=', 'paiement en attente')->first();
                            if(empty($objStatutCmde)) {
                                DB::rollback();
                                $this->_errorCode = 7;
                                $this->_response['message'][] = "Le statut de la commande n'existe pas!";
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Création de la commande
                            //-----------------------------------------------------------------------------------------------
                            try {

                                $objCommande = new Commande();
                                $objCommande->published = 1;
                                $objCommande->lieu_livraison = $objListCommande['lieu_livraison'];
                                $objCommande->generateReference();
                                $objCommande->generateAlias("Commande".$objCommande->id);
                                $objCommande->user_client()->associate($objUser);
                                $objCommande->statut_cmd()->associate($objStatutCmde);
                                $objCommande->ville()->associate($objVille);
                                $objCommande->save();

                            } catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 8;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }



                            try{

                                $objPanier = new Panier();
                                $objPanier->quantite = $objListCommande['quantite'];
                                $objPanier->prix_total = $objListCommande['prix_total'];
                                $objPanier->published = 1;
                                $objPanier->generateReference();
                                $objPanier->generateAlias("Commande".$objCommande->id);
                                $objPanier->commande()->associate($objCommande);
                                $objPanier->produit()->associate($objProduit);
                                $objPanier->save();

                            }catch(Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 9;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            $detailProductColl = collect();
                            if($objListCommande->has('detail_produit')) {

                                foreach($objListCommande['detail_produit'] as $detail) {

                                    if(isset($detail['long']) && isset($detail['stent'])) {

                                        $objLong = Long::where('ref', '=', $detail['long'])->first();

                                        $objLong = Stent::where('ref', '=', $detail['stent'])->first();

                                        $objLongStent = Long_stent::where('long_id', '=', $objLong->id)->where('stent_id', '=', $objLong->id)->first();

                                        try{

                                            $objDetail_panier = new Detail_panier();
                                            if(isset($detail['quantite'])) {
                                                $objDetail_panier->quantite = $detail['quantite'];
                                            }

                                            if(isset($detail['prix_total'])) {
                                                $objDetail_panier->prix_total = $detail['prix_total'];
                                            }
                                            $objDetail_panier->published = 1;
                                            $objDetail_panier->generateReference();
                                            $objDetail_panier->generateAlias("Panier".$objPanier->id);
                                            $objDetail_panier->panier()->associate($objPanier);
                                            $objDetail_panier->long_stent()->associate($objLongStent);
                                            $objDetail_panier->save();

                                        }catch (Exception $objException) {
                                            DB::rollback();
                                            $this->_errorCode = 10;
                                            if (in_array($this->_env, ['local', 'development'])) {
                                            }
                                            $this->_response['message'] = $objException->getMessage();
                                            $this->_response['error_code'] = $this->prepareErrorCode();
                                            return response()->json($this->_response);
                                        }
                                    }

                                    $detailProductColl->push(array(
                                        'detail_product' => $objDetail_panier
                                    ));

                                    /*if(!isset($detail['long']) && !isset($detail['stent']) && isset($detail['sous_produit'])) {

                                        $objSubProduit = Produit::where('ref', '=', $detail['sous_produit'])->first();

                                        try{

                                            $objDetail_panier = new Detail_panier();
                                            if(isset($detail['quantite'])) {
                                                $objDetail_panier->quantite = $detail['quantite'];
                                            }
                                            $objDetail_panier->prix_total = $detail['prix_total'];
                                            $objDetail_panier->published = 1;
                                            $objDetail_panier->generateReference();
                                            $objDetail_panier->generateAlias("Panier".$objPanier->id);
                                            $objDetail_panier->panier()->associate($objPanier);
                                            $objDetail_panier->produit()->associate($objSubProduit);
                                            $objDetail_panier->save();

                                        }catch(Exception $objException) {
                                            DB::rollback();
                                            $this->_errorCode = 10;
                                            if (in_array($this->_env, ['local', 'development'])) {
                                            }
                                            $this->_response['message'] = $objException->getMessage();
                                            $this->_response['error_code'] = $this->prepareErrorCode();
                                            return response()->json($this->_response);
                                        }
                                    }*/

                                }
                            }

                        }else {
                            DB::rollback();
                            $this->_errorCode = 11;
                            $this->_response['message'][] = "Le prix_total n'existe pas!";
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                    }else {
                        DB::rollback();
                        $this->_errorCode = 12;
                        $this->_response['message'][] = "La quantité n'existe pas!";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                    $objUserGestionnaire = User::where('id','=',$objProduit->user_id)->first();
                    if(!in_array($objUserGestionnaire->email, $listGestionnaire)) {
                        $listGestionnaire[] = $objUserGestionnaire->email;
                    }

                }else {
                    DB::rollback();
                    $this->_errorCode = 13;
                    $this->_response['message'][] = "Le produit n'existe pas!";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $dataCustomer = [
                    'commande'=> $objCommande,
                    'customer'=> $objUser,
                    'panier'=> $objPanier,
                    'product'=> $objProduit,
                    'detail_products'=> $detailProductColl
                ];

                /*
                * Shurtcode d'envoi de mail au client
                */

                try {

                    Mail::to($objUser->email)
                    ->send(new OrderOsiroToCustomer($dataCustomer));

                }catch (Exception $objException) {

                    DB::rollBack();
                    $this->_errorCode = 14;
                    if(in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }



                /*
                * Shurtcode d'envoi de mail au gestionnaire
                */
                foreach($listGestionnaire as $userGestionnaire) {
                    $objUserGestionnaire = User::where('email','=',$userGestionnaire)->first();

                    $data = [
                        'gestionnaire'=> $objUserGestionnaire,
                        'customer'=> $objUser,
                        'commande'=> $objCommande,
                        'panier'=> $objPanier,
                        'product'=> $objProduit,
                        'detail_products'=> $detailProductColl
                    ];

                    try {

                        Mail::to($objUserGestionnaire->email)
                        ->send(new OrderOsiroToGestionnaire($data));

                    }catch (Exception $objException) {

                        DB::rollBack();
                        $this->_errorCode = 15;
                        if(in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }
                }

                $objUserAdmin = User::where('role_id','=',1)->first();
                $adminData = [
                    'customer'=> $objUser,
                    'commande'=> $objCommande,
                    'panier'=> $objPanier,
                    'product'=> $objProduit,
                    'detail_products'=> $detailProductColl
                ];

                /*
                * Shurtcode d'envoi de mail à l'admin
                */
                try {

                    Mail::to($objUserAdmin->email)
                    ->send(new OrderOsiroToAdmin($adminData));

                }catch (Exception $objException) {

                    DB::rollBack();
                    $this->_errorCode = 16;
                    if(in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $mail_reponse = 'Email has been sent to gestionnaire, admin and customer';


            }else {
                DB::rollback();
                $this->_errorCode = 17;
                $this->_response['message'][] = "Veuillez entrer un lieu de livraison!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }else {
            DB::rollback();
            $this->_errorCode = 18;
            $this->_response['message'][] = "Veuillez choisir une ville!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        // Commit the queries!
        DB::commit();
        $toReturn = [
            'mail_reponse' => $mail_reponse,
            'objet' => $objCommande
        ];

        $this->_response['message'] = "Commande créée avec succès.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    /**
     * Record payments for orders .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_mode' => 'string|required',
            'ref_commande' => 'string|required',
            'apiKey' => 'string|required',
            'secretKey' => 'string|required',
            'phone' => 'required',
            'montant' => 'required'
        ]);

        if ($validator->fails()){
            if (!empty($validator->errors()->all())){
                foreach ($validator->errors()->all() as $error){
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 2;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        DB::beginTransaction();

        $resultOperateur = (object)[];
        $resultTransaction = (object)[];
        $objTransaction = (object)[];
        $objCommande = (object)[];
        //$message = "";
        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

        if($request->has('ref_mode')) {

            $objMode = Mode::where('ref','=',$request->get('ref_mode'))->first();

            if($request->has('apiKey')) {

                if($request->has('secretKey')) {

                    if($request->has('ref_commande')) {

                        if($request->has('montant')) {

                            if($request->has('phone')) {

                                $phone = $request->get('phone');

                                if(strlen($phone) == 12) {

                                    if(preg_match('/^(237)(\d{3})(\d{3})(\d{3})$/', $phone, $matches)) {

                                        if($objMode->name == 'mtn') {

                                            $phone = $matches[1].$matches[2].$matches[3].$matches[4];

                                        }

                                        if($objMode->name == 'orange') {

                                            $phone = $matches[2].$matches[3].$matches[4];

                                        }
                                    }

                                }elseif(strlen($phone) == 9) {

                                    if(preg_match('/^(6)(\d{2})(\d{3})(\d{3})$/', $phone, $matches)) {

                                        if($objMode->name == 'mtn'){

                                            $phone = "237".$matches[1].$matches[2].$matches[3].$matches[4];

                                        }

                                        if($objMode->name == 'orange'){

                                            $phone = $matches[1].$matches[2].$matches[3].$matches[4];

                                        }
                                    }

                                }else{
                                    DB::rollback();
                                    $this->_errorCode = 3;
                                    $this->_response['message'][] = 'Veuillez saisir un numéro correct.';
                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                    return response()->json($this->_response);
                                }

                                $objCommande = Commande::where('ref','=',$request->get('ref_commande'))->first();

                                //$statutCmd = Statut_cmd::where('id','=',$objCommande->statut_cmd_id)->first();

                                if($objCommande->statut_cmd->name == 'paiement en attente'){

                                    if(intval($request->get('montant')) == 50000) {/**50000 */

                                        try {

                                            $objTransaction = new Transaction();
                                            $objTransaction->paie_phone = $phone;
                                            $objTransaction->montant = $request->get('montant');
                                            $objTransaction->total_payment = $request->get('montant');
                                            $objTransaction->commande()->associate($objCommande);
                                            $objTransaction->statut_transaction()->associate($objStatut_trans);
                                            $objTransaction->mode()->associate($objMode);
                                            $objTransaction->published = 1;
                                            $objTransaction->generateReference();
                                            $objTransaction->generateAlias("transaction".$objTransaction->id);
                                            $objTransaction->save();

                                        }catch (Exception $objException) {
                                            DB::rollback();
                                            $this->_errorCode = 4;
                                            if (in_array($this->_env, ['local', 'development'])) {
                                            }
                                            $this->_response['message'] = $objException->getMessage();
                                            $this->_response['error_code'] = $this->prepareErrorCode();
                                            return response()->json($this->_response);
                                        }

                                        //-----------------------------------------------------------------------------------------------
                                        //Initiation d'un paiement à TasPay
                                        //-----------------------------------------------------------------------------------------------
                                        $postfields = array(
                                            'phone' => $objTransaction->paie_phone,
                                            'montant' => $objTransaction->montant,
                                            'transactionkey' => $objTransaction->ref,
                                            'apiKey' => $request->get("apiKey"),
                                            //'apiKey' => 'QIZUKBCV1FLPTSH',
                                            'secretKey' => $request->get("secretKey"),
                                            //'secretKey' => '9FYXGA4I1ALDP73XMJTJMYDNHQ9CZLGF',
                                            'methode_paiement' => $objMode->name
                                        );

                                        try {

                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/marchand/transaction/create');
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                            curl_setopt($ch, CURLOPT_POST, 1);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            $result = json_decode(curl_exec($ch), true);

                                            //return response()->json($result);


                                        }catch (Exception $objException) {
                                            DB::rollback();
                                            $this->_errorCode = 5;
                                            if (in_array($this->_env, ['local', 'development'])) {
                                            }
                                            $this->_response['message'] = $objException->getMessage();
                                            $this->_response['error_code'] = $this->prepareErrorCode();
                                            return response()->json($this->_response);
                                        }

                                        

                                        if($result != null){

                                            $resultOperateur = $result['data']['operateur'];

                                            if($resultOperateur['name'] == "orange") {

                                                $resultTransaction = $result['data']['transaction'];

                                                try {

                                                    $objTransaction->update(['taspay_transaction' => $resultTransaction['ref']]);


                                                }catch (Exception $objException) {
                                                    DB::rollback();
                                                    $this->_errorCode = 6;
                                                    if (in_array($this->_env, ['local', 'development'])) {
                                                    }
                                                    $this->_response['message'] = $objException->getMessage();
                                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                                    return response()->json($this->_response);
                                                }

                                                /*if($resultTransaction['transaction_status'] == 'SUCCESS') {

                                                    $objStatut_trans = Statut_transaction::where('name','=','reussie')->first();

                                                    try {
                                                        $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $objStatutCmde = Statut_cmd::where('name', '=', 'en progression')->first();

                                                    try {
                                                        $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $objStatutCar = Statut_car::where('name', '=', 'en cours')->first();

                                                    try {
                                                        $objCar->update(['statut_car_id' => $objStatutCar->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    //Editer le statut du restant des commandes appartenant à ce car si elles exitent à annuler
                                                    $otherOrders = Commande::where('id','!=',$objCommande->id)->where('car_id','=',$objCar->id)->get();
                                                    try{
                                                        if($otherOrders->isNotEmpty()) {

                                                            foreach($otherOrders as $order) {
                                                                $order->update(['statut_cmd_id' => 4]);//4:annulé
                                                            }

                                                        }

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $message = 'Success payment';

                                                }elseif($resultTransaction['transaction_status'] == 'FAILED') {

                                                    $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                    try {
                                                        $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $message = 'Fail payment';

                                                }elseif($resultTransaction['transaction_status'] == 'PENDING') {

                                                    //-----------------------------------------------------------------------------------------------
                                                    //Check de paiement Orange
                                                    //-----------------------------------------------------------------------------------------------
                                                    $postfields = array(
                                                        'ref_transaction' => $resultTransaction['ref']
                                                    );

                                                    try {
                                                        $ch = curl_init();
                                                        curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/orange/payment/status/check');
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                                        curl_setopt($ch, CURLOPT_POST, 1);
                                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                        $result = json_decode(curl_exec($ch), true);

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $resultTransaction = $result['data']['objet'];

                                                    if($resultTransaction['transaction_status'] == 'SUCCESS') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','reussie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCmde = Statut_cmd::where('name', '=', 'en progression')->first();

                                                        try {
                                                            $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCar = Statut_car::where('name', '=', 'en cours')->first();

                                                        try {
                                                            $objCar->update(['statut_car_id' => $objStatutCar->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        //Editer le statut du restant des commandes appartenant à ce car si elles exitent à annuler
                                                        $otherOrders = Commande::where('id','!=',$objCommande->id)->where('car_id','=',$objCar->id)->get();
                                                        try{
                                                            if($otherOrders->isNotEmpty()) {

                                                                foreach($otherOrders as $order) {
                                                                    $order->update(['statut_cmd_id' => 4]);//4:annulé
                                                                }

                                                            }

                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Success payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'FAILED') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Fail payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'PENDING') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Payment in progress';

                                                    }elseif($resultTransaction['transaction_status'] == 'INITIATED') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Payment initiated';

                                                    }elseif($resultTransaction['transaction_status'] == 'EXPIRED') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'payment expired';

                                                    }else{
                                                        DB::rollback();
                                                        return response()->json("Statut inexistant!");
                                                    }

                                                }elseif ($resultTransaction['transaction_status'] == 'INITIATED') {

                                                    //-----------------------------------------------------------------------------------------------
                                                    //Check de paiement Orange
                                                    //-----------------------------------------------------------------------------------------------
                                                    $postfields = array(
                                                        'ref_transaction' => $resultTransaction['ref']
                                                    );

                                                    try {
                                                        $ch = curl_init();
                                                        curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/orange/payment/status/check');
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                                        curl_setopt($ch, CURLOPT_POST, 1);
                                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                        $result = json_decode(curl_exec($ch), true);

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $resultTransaction = $result['data']['objet'];

                                                    if($resultTransaction['transaction_status'] == 'SUCCESS') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','reussie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCmde = Statut_cmd::where('name', '=', 'en progression')->first();

                                                        try {
                                                            $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCar = Statut_car::where('name', '=', 'en cours')->first();

                                                        try {
                                                            $objCar->update(['statut_car_id' => $objStatutCar->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        //Editer le statut du restant des commandes appartenant à ce car si elles exitent à annuler
                                                        $otherOrders = Commande::where('id','!=',$objCommande->id)->where('car_id','=',$objCar->id)->get();
                                                        try{
                                                            if($otherOrders->isNotEmpty()) {

                                                                foreach($otherOrders as $order) {
                                                                    $order->update(['statut_cmd_id' => 4]);//4:annulé
                                                                }

                                                            }

                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Success payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'FAILED') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }
                                                        $message = 'Fail payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'PENDING') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Payment in progress';

                                                    }elseif($resultTransaction['transaction_status'] == 'INITIATED') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Payment initiated';

                                                    }elseif($resultTransaction['transaction_status'] == 'EXPIRED') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Fail payment';

                                                    }else{
                                                        DB::rollback();
                                                        return response()->json("No status!");
                                                    }

                                                }elseif ($resultTransaction['transaction_status'] == 'EXPIRED') {


                                                    $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                    try {
                                                        $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $message = 'Fail payment';

                                                }else{
                                                    DB::rollback();
                                                    return response()->json("No statut!");
                                                }*/

                                            }elseif($resultOperateur['name'] == "mtn") {

                                                $resultTransaction = $result['data']['transaction'];

                                                try {
                                                    $objTransaction->update(['taspay_transaction' => $resultTransaction['ref']]);
                                                }catch (Exception $objException) {
                                                    DB::rollback();
                                                    $this->_errorCode = 7;
                                                    if (in_array($this->_env, ['local', 'development'])) {
                                                    }
                                                    $this->_response['message'] = $objException->getMessage();
                                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                                    return response()->json($this->_response);
                                                }


                                                /*if($resultTransaction['transaction_status'] == 'SUCCESSFUL') {
                                                    $objStatut_trans = Statut_transaction::where('name','=','reussie')->first();

                                                    try {
                                                        $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $objStatutCmde = Statut_cmd::where('name', '=', 'en progression')->first();

                                                    try {
                                                        $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $objStatutCar = Statut_car::where('name', '=', 'en cours')->first();

                                                    try {
                                                        $objCar->update(['statut_car_id' => $objStatutCar->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    //Editer le statut du restant des commandes appartenant à ce car si elles exitent à annuler
                                                    $otherOrders = Commande::where('id','!=',$objCommande->id)->where('car_id','=',$objCar->id)->get();
                                                    try{
                                                        if($otherOrders->isNotEmpty()) {

                                                            foreach($otherOrders as $order) {
                                                                $order->update(['statut_cmd_id' => 4]);//4:annulé
                                                            }

                                                        }

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $message = 'Success payment';

                                                }elseif($resultTransaction['transaction_status'] == 'FAILED') {
                                                    $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                    try {
                                                        $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $message = 'Fail payment';

                                                }elseif($resultTransaction['transaction_status'] == 'PENDING') {

                                                    //-----------------------------------------------------------------------------------------------
                                                    //Check de paiement Mtn
                                                    //-----------------------------------------------------------------------------------------------
                                                    $postfields = array(
                                                        'ref_transaction' => $resultTransaction['ref']
                                                    );

                                                    try {
                                                        $ch = curl_init();
                                                        curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/mtn/payment/status/check');
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                                        curl_setopt($ch, CURLOPT_POST, 1);
                                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                                        $result = json_decode(curl_exec($ch), true);

                                                    }catch (Exception $objException) {
                                                        DB::rollback();
                                                        return response()->json($objException->getMessage());
                                                    }

                                                    $resultTransaction = $result['data']['objet'];

                                                    if($resultTransaction['transaction_status'] == 'SUCCESSFUL') {
                                                        $objStatut_trans = Statut_transaction::where('name','=','reussie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCmde = Statut_cmd::where('name', '=', 'en progression')->first();

                                                        try {
                                                            $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $objStatutCar = Statut_car::where('name', '=', 'en cours')->first();

                                                        try {
                                                            $objCar->update(['statut_car_id' => $objStatutCar->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        //Editer le statut du restant des commandes appartenant à ce car si elles exitent à annuler
                                                        $otherOrders = Commande::where('id','!=',$objCommande->id)->where('car_id','=',$objCar->id)->get();
                                                        try{
                                                            if($otherOrders->isNotEmpty()) {

                                                                foreach($otherOrders as $order) {
                                                                    $order->update(['statut_cmd_id' => 4]);//4:annulé
                                                                }

                                                            }

                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Success payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'FAILED') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','echoue')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Fail payment';

                                                    }elseif($resultTransaction['transaction_status'] == 'PENDING') {

                                                        $objStatut_trans = Statut_transaction::where('name','=','initie')->first();

                                                        try {
                                                            $objTransaction->update(['statut_transaction_id' => $objStatut_trans->id]);
                                                        }catch (Exception $objException) {
                                                            DB::rollback();
                                                            return response()->json($objException->getMessage());
                                                        }

                                                        $message = 'Payment in progress';

                                                    }else{
                                                        DB::rollback();
                                                        return response()->json("Statut inexistant!");
                                                    }

                                                }else{
                                                    DB::rollback();
                                                    return response()->json("No status!");
                                                }*/

                                            }else {
                                                DB::rollback();
                                                $this->_errorCode = 8;
                                                $this->_response['message'][] = 'Absence de paramètre de paiement.';
                                                $this->_response['error_code'] = $this->prepareErrorCode();
                                                return response()->json($this->_response);
                                                DB::rollback();
                                                return response()->json("missing payement parameter.");
                                            }

                                        }else{
                                            DB::rollback();
                                            $this->_errorCode = 9;
                                            $this->_response['message'][] = 'Aucune donnée retournée par'.$objMode->name;
                                            $this->_response['error_code'] = $this->prepareErrorCode();
                                            return response()->json($this->_response);
                                        }


                                    }else{
                                        DB::rollback();
                                        $this->_errorCode = 10;
                                        $this->_response['message'][] = 'L\'avance à verser doit s\'éléver à 500 000FCFA.';
                                        $this->_response['error_code'] = $this->prepareErrorCode();
                                        return response()->json($this->_response);
                                    }

                                }else{
                                    DB::rollback();
                                    $this->_errorCode = 11;
                                    $this->_response['message'][] = 'Vous ne pouvez effectuer un paiement sur cette commande.';
                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                    return response()->json($this->_response);
                                }


                            }else{
                                DB::rollback();
                                $this->_errorCode = 12;
                                $this->_response['message'][] = 'Veuillez entrer le numéro de téléphone par lequel paiement se fera.';
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                        }else{
                            DB::rollback();
                            $this->_errorCode = 13;
                            $this->_response['message'][] = 'Veuillez entrer le montant à payer.';
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                    }else{
                        DB::rollback();
                        $this->_errorCode = 14;
                        $this->_response['message'][] = 'Le ref de la commande n\'est pas donné.';
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                }else{
                    DB::rollback();
                    $this->_errorCode = 15;
                    $this->_response['message'][] = 'La clé sécrète n\'est pas donné.';
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{
                DB::rollback();
                $this->_errorCode = 16;
                $this->_response['message'][] = 'La clé d\'api n\'est pas donné.';
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }else{
            DB::rollback();
            $this->_errorCode = 17;
			$this->_response['message'][] = 'Veuillez choisir un mode de paiement.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        DB::commit();
        $toReturn = [
            'taspay_transaction'=> $resultTransaction,
            'commande' => $objCommande,
            'operateur' => $resultOperateur,
            'transaction' => $objTransaction/*,
            'statut_trans'=>$objStatut_trans*/
        ];

        $this->_response['message'] = "Enregistrement du paiement inité!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Fonction qui permet de checker une transaction par le moyen Orange Money
    public function checkTransactionOrange(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_transaction' => 'string|required'
        ]);

        if($validator->fails()){
            if (!empty($validator->errors()->all())){
                foreach ($validator->errors()->all() as $error){
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        $objTransaction = Transaction::where('ref','=',$request->get('ref_transaction'))->first();


        $objStatut_transaction = Statut_transaction::where('id', '=', $objTransaction->statut_trans_id)->first();
        DB::beginTransaction();

        $resultTransaction = (object)[];
        $objCommande = (object)[];
        $message = "";
        $responseMail = "";

        if($objStatut_transaction->name == "reussie") {
            $message = "La transaction est déjà reussie!";
        }

        if($objStatut_transaction->name == "echouer") {
            $message = "La transaction est déjà échouée!";
        }


        if($objStatut_transaction->name == "initie") {
            //------------------------------------------------------------------------------------------------------
            //Check de paiement Orange
            //------------------------------------------------------------------------------------------------------
            $postfields = array(
                'ref_transaction' => $objTransaction->taspay_transaction
            );

            try{

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/orange/payment/status/check');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = json_decode(curl_exec($ch), true);



            }catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 2;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            $resultTransaction = $result['data']['objet'];

            if($resultTransaction['transaction_status'] == 'INITIATED') {
                $message = "Votre paiement est inité chez Orange!";
            }

            if($resultTransaction['transaction_status'] == 'PENDING') {
                $message = "Le paiement est en progression chez Orange.";
            }

            if($resultTransaction['transaction_status'] == 'SUCCESS') {

                //-----------------------------------------------------------------------------------------------
                //Statut_transaction : reussie
                //-----------------------------------------------------------------------------------------------
                $objStatut_transaction = Statut_transaction::where('name', '=', 'reussie')->first();

                try {
                    $objTransaction->update(['statut_trans_id' => $objStatut_transaction->id]);
                }catch(Exception $objException) {
                    DB::rollback();
                    $this->_errorCode = 3;
                    if (in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $objStatutCmde = Statut_cmd::where('name', '=', 'paiement partiel')->first();
                if(empty($objStatutCmde)) {
                    DB::rollback();
                    $this->_errorCode = 4;
                    $this->_response['message'][] = "Le statut de la commande n'existe pas!";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $objCommande = Commande::where('id','=',$objTransaction->commande_id)->first();

                try {
                    $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                }catch(Exception $objException) {
                    DB::rollback();
                    $this->_errorCode = 5;
                    if (in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }



                /**
                 * Code pour générer la facture au client en attente
                 */
                $objTransaction->downloadPDF($objTransaction->id);

                $message = "Transaction reussie!";

            }

            if($resultTransaction['transaction_status'] == 'FAILED') {
                //-----------------------------------------------------------------------------------------------
                //Statut_transaction : echoue
                //-----------------------------------------------------------------------------------------------
                $objStatut_transaction = Statut_transaction::where('name', '=', 'echouer')->first();

                try {
                    $objTransaction->update(['statut_trans_id' => $objStatut_transaction->id]);
                }catch (Exception $objException) {
                    DB::rollback();
                    $this->_errorCode = 6;
                    if (in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $message = "Transaction échouée!";

            }

            if($resultTransaction['transaction_status'] == 'EXPIRED') {

                $message = "Le paiement est expiré!";

            }
        }


        DB::commit();
        $toReturn = [
            'commande' => $objCommande,
            'transaction' => $objTransaction,
            'taspay_info' => $resultTransaction
        ];
        $this->_response['message'] = $message;
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Fonction qui permet de checker une transaction reussie par le moyen Mtn Money
    public function checkTransactionMtn(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_transaction' => 'string|required'
        ]);

        if ($validator->fails()){
            if (!empty($validator->errors()->all())){
                foreach ($validator->errors()->all() as $error){
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objTransaction = Transaction::where('ref','=',$request->get('ref_transaction'))->first();

        $objStatut_transaction = Statut_transaction::where('id', '=', $objTransaction->statut_trans_id)->first();

        DB::beginTransaction();

        $resultTransaction = (object)[];
        $message = "";

        if($objStatut_transaction->name == "reussie") {
            $message = "La transaction est déjà reussie!";
        }

        if($objStatut_transaction->name == "echouer") {
            $message = "La transaction est déjà échouée!";
        }

        if($objStatut_transaction->name == "initie") {
            //-----------------------------------------------------------------------------------------------
            //Check de paiement Mtn
            //-----------------------------------------------------------------------------------------------
            $postfields = array(
                'ref_transaction' => $objTransaction->taspay_transaction
            );

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/mtn/payment/status/check');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = json_decode(curl_exec($ch), true);

            }catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 2;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
            }


            if($result != null) {

                $resultTransaction = $result['data']['objet'];

                if($resultTransaction['transaction_status'] == 'PENDING') {

                    $message = "Le paiement est en cours chez MTN.";

                }

                if($resultTransaction['transaction_status'] == 'SUCCESSFUL') {
                    //-----------------------------------------------------------------------------------------------
                    //Statut_transaction : reussie
                    //-----------------------------------------------------------------------------------------------
                    $objStatut_transaction = Statut_transaction::where('name', '=', 'reussie')->first();

                    try {
                        $objTransaction->update(['statut_trans_id' => $objStatut_transaction->id]);
                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 3;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                    }

                    $objStatutCmde = Statut_cmd::where('name', '=', 'paiement partiel')->first();
                    if(empty($objStatutCmde)) {
                        DB::rollback();
                        $this->_errorCode = 4;
                        $this->_response['message'][] = "Le statut de la commande n'existe pas!";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                    $objCommande = Commande::where('id','=',$objTransaction->commande_id)->first();

                    try {
                        $objCommande->update(['statut_cmd_id' => $objStatutCmde->id]);
                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 5;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                    }


                    $objTransaction->downloadPDF($objTransaction->id);


                    $message = "Transaction reussie!";

                }

                if($resultTransaction['transaction_status'] == 'FAILED') {
                    //-----------------------------------------------------------------------------------------------
                    //Statut_transaction : echoue
                    //-----------------------------------------------------------------------------------------------
                    $objStatut_transaction = Statut_transaction::where('name', '=', 'echouer')->first();

                    try {
                        $objTransaction->update(['statut_trans_id' => $objStatut_transaction->id]);
                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 6;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                    }

                    $message = "Transaction échouée!";

                }

            }else{
                DB::rollback();
                $this->_errorCode = 4;
                $this->_response['message'][] = "Aucune donnée retournée par MTN!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }


        DB::commit();
        $toReturn = [
            'commande' => $objCommande,
            'transaction' => $objTransaction,
            'taspay_info' => $resultTransaction
        ];
        $this->_response['message'] = $message;
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Fonction qui permet de recupérer la liste des commandes d'un customer
    public function customerOrderslist()
    {
        $this->_fnErrorCode = 1;

        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 2;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        //On vérifie le rôle client
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != "client") {
			$this->_errorCode = 3;
			$this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        DB::beginTransaction();

        try {

            $objAllCommandes = Commande::where('user_client_id', $objUser->id)->with('user_client','statut_cmd','suivi','ville','user_livreur','user_gestionnaire')
            ->orderBy('commandes.id', 'desc')
            ->get();


        } catch (Exception $objException) {
            DB::rollback();
            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        //return response()->json($objAllCommandes);

        $collCommandes = collect();
        foreach($objAllCommandes as $commande){

            /**Check commande en attente de paiement*/
            if($commande->statut_cmd->name == 'paiement en attente') {

                /**Recup la transaction initiée */
                $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',1)->with('mode')->first();

                if(!empty($objTransaction)) {

                    $commande = Commande::where('id','=',$commande->id)->first();

                    /**Vérif du mode de paiement */
                    if($objTransaction->mode->name == 'orange') {

                        $postfields = array(
                            'ref_transaction' => $objTransaction->taspay_transaction
                        );

                        try {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/orange/payment/status/check');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            $result = json_decode(curl_exec($ch), true);

                        }catch (Exception $objException) {
                            DB::rollback();
                            $this->_errorCode = 5;
                            if (in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                        //$objTransaction = Transaction::where('id','=',$objTransaction->id)->first();

                        $resultTransaction = $result['data']['objet'];

                        if($resultTransaction['transaction_status'] == 'FAILED') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : echouer
                            //-----------------------------------------------------------------------------------------------
                            try {
                                $objTransaction->update(['statut_trans_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 6;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                        }

                        if($resultTransaction['transaction_status'] == 'SUCCESS') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : reussie
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $objTransaction->update(['statut_trans_id' => 3]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 7;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Statut_cmd : paiement partiel
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $commande->update(['statut_cmd_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 8;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }


                        }

                    }

                    if($objTransaction->mode->name == 'mtn') {

                        $postfields = array(
                            'ref_transaction' => $objTransaction->taspay_transaction
                        );

                        try {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/mtn/payment/status/check');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            $result = json_decode(curl_exec($ch), true);

                        }catch (Exception $objException) {
                            DB::rollback();
                            $this->_errorCode = 9;
                            if (in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                        $resultTransaction = $result['data']['objet'];

                        if($resultTransaction['transaction_status'] == 'FAILED') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : echouer
                            //-----------------------------------------------------------------------------------------------
                            try {
                                $objTransaction->update(['statut_trans_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 10;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }
                        }


                        if($resultTransaction['transaction_status'] == 'SUCCESSFUL') {

                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : reussie
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $objTransaction->update(['statut_trans_id' => 3]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 11;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Statut_cmd : paiement partiel
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $commande->update(['statut_cmd_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 12;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                        }

                    }

                }

            }

            $allPaniers = Panier::where('commande_id','=',$commande->id)->with('produit', 'produit.produitImgs')->get();

            /**Recupère le montant de la commande passée */
            $MontanCommande = Panier::where('commande_id','=',$commande->id)->sum('prix_total');

            $collDetail = collect();
            $montant = 0;
            $montant_restant_a_payer = 0;
            foreach($allPaniers as $item) {
                $montant = intval($item->prix_total) + $montant;
                try {

                    $objDetailPanier = Detail_panier::where('panier_id','=',$item->id)->with('produit','produit.produitImgs','long_stent','long_stent.long','long_stent.stent')->get();

                }catch(Exception $objException) {
                    $this->_fnErrorCode = 13;
                    if (in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $collDetail->push(array(
                    'panier' => $item,
                    'detail_panier' => $objDetailPanier
                ));

            }
            $total_paiement = 0;

            $allTransactions = Transaction::where('commande_id','=',$commande->id)->with('statut_transaction','mode')->get();
            $commande = Commande::where('id','=',$commande->id)->with('statut_cmd')->first();
            
            $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',3)->first();
            if (!empty($objTransaction)) {
                $total_paiement = $objTransaction->total_payment;
            }
            $montant_restant_a_payer = $montant - intval($total_paiement);
            $collCommandes->push(array(
                'commande'=> $commande,
                'montant_commande'=> $MontanCommande,
                'all_panier'=> $collDetail,
                'transaction'=> $allTransactions,
                'reste_a_payer' => $montant_restant_a_payer
            ));

        }

        //return response()->json($collCommandes);


        //dd($collCommandes);

        DB::commit();
        $toReturn = [
            'objet' => $collCommandes
        ];
        $this->_response['message'] = "Liste des Commandes d'un client.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Fonction qui permet au Gestionnaire| Admin de voir la liste des commandes des customers
    public function Orderslist()
    {
        $this->_fnErrorCode = 1;

        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 2;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        //On vérifie le rôle gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if(!in_array($objRole->alias,array('gestionnaire','admin'))) {
			$this->_errorCode = 3;
			$this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        DB::beginTransaction();

        try {

            $objAllCommandes = Commande::with('user_client','statut_cmd','suivi','ville','user_livreur','user_gestionnaire')
            ->orderBy('commandes.id', 'desc')
            ->get();

        } catch (Exception $objException) {
            DB::rollback();
            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $collCommandes = collect();
        foreach($objAllCommandes as $commande){

            /**Check commande en attente */
            if($commande->statut_cmd->name == 'paiement en attente') {

                /**Recup la transaction initiée */
                $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',1)->with('mode')->first();

                if(!empty($objTransaction)) {

                    $commande = Commande::where('id','=',$commande->id)->first();

                    /**Vérif du mode de paiement */
                    if($objTransaction->mode->name == 'orange') {

                        $postfields = array(
                            'ref_transaction' => $objTransaction->taspay_transaction
                        );

                        try {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/orange/payment/status/check');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            $result = json_decode(curl_exec($ch), true);

                        }catch (Exception $objException) {
                            DB::rollback();
                            $this->_errorCode = 5;
                            if (in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                        //$objTransaction = Transaction::where('id','=',$objTransaction->id)->first();

                        $resultTransaction = $result['data']['objet'];

                        if($resultTransaction['transaction_status'] == 'FAILED') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : echouer
                            //-----------------------------------------------------------------------------------------------
                            try {
                                $objTransaction->update(['statut_trans_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 6;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                        }

                        if($resultTransaction['transaction_status'] == 'SUCCESS') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : reussie
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $objTransaction->update(['statut_trans_id' => 3]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 7;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Statut_cmd : paiement partiel
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $commande->update(['statut_cmd_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 8;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }


                        }

                    }

                    if($objTransaction->mode->name == 'mtn') {

                        $postfields = array(
                            'ref_transaction' => $objTransaction->taspay_transaction
                        );

                        try {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://taspay.team-solutions.net/api/api/mtn/payment/status/check');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            $result = json_decode(curl_exec($ch), true);

                        }catch (Exception $objException) {
                            DB::rollback();
                            $this->_errorCode = 9;
                            if (in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                        $resultTransaction = $result['data']['objet'];

                        if($resultTransaction['transaction_status'] == 'FAILED') {
                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : echouer
                            //-----------------------------------------------------------------------------------------------
                            try {
                                $objTransaction->update(['statut_trans_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 10;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }
                        }


                        if($resultTransaction['transaction_status'] == 'SUCCESSFUL') {

                            //-----------------------------------------------------------------------------------------------
                            //Statut_trans : reussie
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $objTransaction->update(['statut_trans_id' => 3]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 11;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                            //-----------------------------------------------------------------------------------------------
                            //Statut_cmd : paiement partiel
                            //-----------------------------------------------------------------------------------------------

                            try {
                                $commande->update(['statut_cmd_id' => 2]);
                            }catch (Exception $objException) {
                                DB::rollback();
                                $this->_errorCode = 12;
                                if (in_array($this->_env, ['local', 'development'])) {
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json($this->_response);
                            }

                        }

                    }

                }

            }

            $allPaniers = Panier::where('commande_id','=',$commande->id)->with('produit','produit.produitImgs')->get();

            /**Recupère le montant de la commande passée */
            $MontanCommande = Panier::where('commande_id','=',$commande->id)->sum('prix_total');

            $collDetail = collect();
            $montant = 0;
            $montant_restant_a_payer = 0;
            foreach ($allPaniers as $item) {
                $montant = intval($item->prix_total) + $montant;
                try {

                    $objDetailPanier = Detail_panier::where('panier_id','=',$item->id)->with('produit','produit.produitImgs','long_stent','long_stent.long','long_stent.stent')->get();

                }catch(Exception $objException) {
                    $this->_fnErrorCode = 13;
                    if (in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $collDetail->push(array(
                    'panier' => $item,
                    'detail_panier' => $objDetailPanier
                ));

            }

            $total_paiement = 0;

            $allTransactions = Transaction::where('commande_id','=',$commande->id)->with('statut_transaction','mode')->get();
            //$commande = Commande::where('id','=',$commande->id)->with('statut_cmd','user_client')->first();
            $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',3)->first();
            
            if (!empty($objTransaction)) {
                $total_paiement = $objTransaction->total_payment;
            }
            
            $montant_restant_a_payer = $montant - intval($total_paiement);
            $collCommandes->push(array(
                'commande'=> $commande,
                'montant_commande'=> $MontanCommande,
                'all_panier'=> $collDetail,
                'transaction'=> $allTransactions,
                'reste_a_payer' => $montant_restant_a_payer
            ));

        }


        //dd($collCommandes);

        DB::commit();
        $toReturn = [
            'objet' => $collCommandes
        ];
        $this->_response['message'] = "Liste global des Commandes.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function orderDetail(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_commande' => 'string|required'
        ]);

        if ($validator->fails()){
            if (!empty($validator->errors()->all())){
                foreach ($validator->errors()->all() as $error){
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objCommande = Commande::where('ref','=',$request->get('ref_commande'))->first();
        if(empty($objCommande)){
            $this->_errorCode = 3;
			$this->_response['message'][] = 'La commande n\'existe pas.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }


        try {

            $objOrderPanier = Panier::where('commande_id','=',$objCommande->id)->with('produit','produit.produitImgs')->get();

        }catch (Exception $objException) {
            $this->_fnErrorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $collDetail = collect();
        foreach ($objOrderPanier as $item) {

            try {

                $objDetailPanier = Detail_panier::where('panier_id','=',$item->id)->with('produit','long_stent','long_stent.long','long_stent.stent')->get();

            }catch(Exception $objException) {
                $this->_fnErrorCode = 5;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            $collDetail->push(array(
                'panier' => $item,
                'detail_panier' => $objDetailPanier
            ));

        }


        try{

            $allTransactions = Transaction::where('commande_id','=',$objCommande->id)->with('mode','statut_transaction')->get();

        }catch (Exception $objException) {
            $this->_fnErrorCode = 5;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $prixTotalPanier = 0;
        $montant_restant_a_payer = 0;

        if($objOrderPanier->isNotEmpty()) {

            $montant = 0;
            foreach($objOrderPanier as $panier) {

                $prixTotalPanier = intval($panier->prix_total) + $montant;

            }

            if($allTransactions->isNotEmpty()) {

                $montantTotalTransaction = 0;
                foreach($allTransactions as $item) {

                    if($item->statut_transaction->name == 'reussie') {

                        $montantTotalTransaction = intval($item->montant) + $montantTotalTransaction;
                    }

                }

                if($prixTotalPanier > $montantTotalTransaction) {
                    $montant_restant_a_payer = $prixTotalPanier - $montantTotalTransaction;
                }

                if($prixTotalPanier == $montantTotalTransaction) {
                    $montant_restant_a_payer = $prixTotalPanier - $montantTotalTransaction;
                }

            }else {
                $montant_restant_a_payer = $prixTotalPanier;
            }

        }

        DB::commit();
        $toReturn = [
            'all_paniers' => $collDetail,
            'transaction' => $allTransactions,
            'montant_restant_a_payer' => $montant_restant_a_payer
        ];
        $this->_response['message'] = 'Détail d\'une Commande.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);

    }

    public function paymentModeList()
    {
        $this->_fnErrorCode = 1;
        try {
            //Récupération liste des modes de paiement
            $objMode = Mode::all();
            $modeColl = collect();
            foreach($objMode as $item) {

                if($item->name == 'orange'){
                    $modeColl->push($item);
                }

                if($item->name == 'mtn'){
                    $modeColl->push($item);
                }

            }
        }catch (Exception $objException) {
            $this->_errorCode = 2;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        $toReturn = [
            'objet'=> $modeColl
        ];
        $this->_response['message'] = 'Liste des modes de paiements.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function ordersAssignToDeliveryman(Request $request){

        $this->_fnErrorCode = 1;

        //On vérifie que la commande est bien envoyé !
        $objListCommande = collect(json_decode($request->getContent(), true));
        if (empty($objListCommande)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste des produits de la commande est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        //On vérife que l'utilisateur est bien connecté
        $objUser = Auth::user();
        if (empty($objUser)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "Utilisateur non connecté";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        //On vérifie que l'utilisateur est bien gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if ($objRole->alias != 'gestionnaire') {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'êtes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();
        $mail_reponse = '';


        if($objListCommande->has('livreur')) {

            $objDeliveryMan = User::where('ref', '=', $objListCommande['livreur'])->first();
            if(empty($objDeliveryMan)) {
                DB::rollback();
                $this->_errorCode = 5;
                $this->_response['message'][] = "Le Livreur n'existe pas !";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            if($objListCommande->has('commandes')) {

                foreach($objListCommande['commandes'] as $item) {

                    $objCommande = Commande::where('ref', '=',$item['commande'])->first();

                    try{

                        $objCommande->update(['user_gestionnaire_id' => $objUser->id]);

                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 6;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                    try{

                        $objCommande->update(['user_livreur_id' => $objDeliveryMan->id]);

                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 7;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }
                }

                /*
                * Shurtcode d'envoi de mail au livreur
                */

                try {

                    Mail::to($objDeliveryMan->email)
                    ->send(new SendDeliverymanMail($objDeliveryMan));

                    $mail_reponse = 'Email has been sent to deliveryman';

                }catch (Exception $objException) {

                    DB::rollBack();
                    $this->_errorCode = 8;
                    if(in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{

                DB::rollback();
                $this->_errorCode = 9;
                $this->_response['message'][] = "Veuillez renseigner la ou les commandes.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);

            }

        }else{

            DB::rollback();
            $this->_errorCode = 10;
            $this->_response['message'][] = "Veuillez renseigner un livreur.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);

        }

        DB::commit();

        $toReturn = [
            'response_mail'=>$mail_reponse
        ];

        $this->_response['message'] = "Le livreur a été assigné avec succès ! ";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    /**
     * Fonction qui permet de gérer le suivi d'une commande
     * par le livreur et gestionnaire
     */
    public function followUpOrderByUser(Request $request) {

        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_commande' => 'string|required',
            'ref_suivi' => 'string|required'
        ]);

        if ($validator->fails()){
            if (!empty($validator->errors()->all())){
                foreach ($validator->errors()->all() as $error){
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 3;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        $objCommande = Commande::where('ref', '=', $request->get('ref_commande'))->with('statut_cmd','suivi')->first();
        if(empty($objCommande)){
            $this->_errorCode = 4;
			$this->_response['message'][] = 'La commande n\'existe pas.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        $objSuivi = Suivi::where('ref', '=', $request->get('ref_suivi'))->first();
        if(empty($objSuivi)){
            $this->_errorCode = 5;
			$this->_response['message'][] = 'Le suivi n\'existe pas.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }


        //On vérifie le rôle de l'utilisateur
        $objRole = Role::where('id', '=', $objUser->role_id)->first();

        if($objRole->alias == 'livreur') {

            if($objCommande->statut_cmd->name == 'paiement terminé') {

                if($objCommande->suivi_cmd->name == 'collecte' && in_array($objSuivi->name, array('transport'))) {

                    try{

                        $objCommande->update(['suivi_cmd_id' => $objSuivi->id]);

                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 6;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                }elseif($objCommande->suivi_cmd->name == 'transport' && in_array($objSuivi->name, array('livré'))) {

                    try{

                        $objCommande->update(['suivi_cmd_id' => $objSuivi->id]);

                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 7;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                }else {
                    DB::rollback();
                    $this->_errorCode = 8;
                    $this->_response['message'][] = "Aucun suivi ne correspond.";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{
                DB::rollback();
                $this->_errorCode = 9;
                $this->_response['message'][] = "Vous ne pouvez effectuer une action sur cette commande.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }
		}


        if($objRole->alias == 'gestionnaire'){

            if($objCommande->statut_cmd->name == 'paiement terminé') {

                if($objCommande->suivi_cmd_id == null && in_array($objSuivi->name, array('collecte'))) {

                    try{

                        $objCommande->update(['suivi_cmd_id' => $objSuivi->id]);

                    }catch (Exception $objException) {
                        DB::rollback();
                        $this->_errorCode = 10;
                        if (in_array($this->_env, ['local', 'development'])) {
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                }else {
                    DB::rollback();
                    $this->_errorCode = 11;
                    $this->_response['message'][] = "Aucun suivi ne correspond.";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{
                DB::rollback();
                $this->_errorCode = 12;
                $this->_response['message'][] = "Vous ne pouvez effectuer une action sur cette commande.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }

        DB::commit();

        $toReturn = [
            'commande'=>$objCommande
        ];

        $this->_response['message'] = "Une occurence de suivi a été assignée avec succès à la commande! ";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);


    }

    public function paymentModesOthersList()
    {
        $this->_fnErrorCode = 1;
        try {
            //Récupération liste des modes de paiement
            $objMode = Mode::all();
            $modeColl = collect();
            foreach($objMode as $item) {

                if(!in_array($item->name, array('orange','mtn'))){
                    $modeColl->push($item);
                }

            }
        }catch (Exception $objException) {
            $this->_errorCode = 2;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        $toReturn = [
            'objet'=> $modeColl
        ];
        $this->_response['message'] = 'Liste des modes de paiements.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    /*
    * Fonction permettant d'afficher
    * la liste des suivis de commande
     */
    public function followUpOrderList()
    {
        $this->_fnErrorCode = 1;
        try {
            //Récupération liste des suivis
            $objSuivi = Suivi::all();
            $gestionnaireSuiviColl = collect();
            $livreurSuiviColl = collect();
            foreach($objSuivi as $item) {

                if($item->name == 'collecte'){
                    $gestionnaireSuiviColl->push($item);
                }

                if(in_array($item->name, array('transport', 'livré'))){
                    $livreurSuiviColl->push($item);
                }

            }
        }catch (Exception $objException) {
            $this->_errorCode = 2;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        $toReturn = [
            'gestionnaire_suivi'=> $gestionnaireSuiviColl,
            'livreur_suivi'=> $livreurSuiviColl
        ];
        $this->_response['message'] = 'Liste des modes de paiements.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Fonction qui permet au livreur de voir la liste des commandes payées qui lui ont été assignées
    public function payOrderslist()
    {
        $this->_fnErrorCode = 1;

        $objUser = Auth::user();
        if(empty($objUser)){
            $this->_errorCode = 2;
			$this->_response['message'][] = 'Cette action nécéssite une connexion.';
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
        }

        //On vérifie le rôle gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if(!in_array($objRole->alias,array('livreur'))) {
			$this->_errorCode = 3;
			$this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        try {

            $objAllCommandesPaid = Commande::with('user_client','statut_cmd','suivi','ville','user_gestionnaire')
            ->where('commandes.user_livreur_id', $objUser->id)
            ->where('commandes.statut_cmd_id', 4)
            ->orderBy('commandes.id', 'desc')
            ->get();

        } catch (Exception $objException) {
            DB::rollback();
            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::commit();
        $toReturn = [
            'objet' => $objAllCommandesPaid
        ];
        $this->_response['message'] = "Liste des Commandes payées et assignées au livreur.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //signature du client
    public function signCustomer(Request $request){
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_commande' => 'string|required',
            'signature' => 'string|required'
        ]);

        //Vérification des paramètres
        if ($validator->fails()) {
            if (!empty($validator->errors()->all())) {
                foreach ($validator->errors()->all() as $error) {
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        $objUser = Auth::user();
        if(empty($objUser)){
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }
        //On vérifie que l'utilisateur est bien gestionnaire du centre
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != 'livreur') {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objCommande = Commande::where('ref', '=', $request->get('ref_commande'))->first();
        if(empty($objCommande)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "La commande n'existe pas !";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try{
            $image = $request->get('signature');  // your base64 encoded
            $extension = explode('/', mime_content_type($request->get('signature')))[1];
            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = "signature_". date('D_M_Y_mhs') . '.'.$extension;

            if(Storage::disk('signature_client')->put($imageName, base64_decode($image))) {

                try{
                    //Mise à jour de la propriété statut_commande_id de la commande
                    $objCommande->update(["signature_client" =>'cardio-afrique/storage/app/public/images/signature/'.$imageName]);
                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 6;
                    if (in_array($this->_env, ['local', 'development'])) {
                        $this->_response['message'] = $objException->getMessage();
                    }
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else {
                DB::rollback();
                $this->_errorCode = 7;
                $this->_response['message'][] = "Echec enregistrement de l'image !";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        } catch (Exception $objException) {
            DB::rollback();
            $this->_errorCode = 8;
            if (in_array($this->_env, ['local', 'development'])) {
                $this->_response['message'] = $objException->getMessage();
            }
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        // Commit the queries!
        DB::commit();
        $toReturn = [
            "objet" => $objCommande
        ];
        $this->_response['message'] = "Commande livée!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    /**Test mail avec MailJet */
    public function testMailJet(){
        $this->_fnErrorCode = 1;

        $details = [
            'title' => 'Mail from cardio',
            'body' => 'This is for testing email using smtp'
        ];

        Mail::to('famillekouloung@gmail.com','sock joel')->send(new \App\Mail\MyTestMail($details));
    }


}
