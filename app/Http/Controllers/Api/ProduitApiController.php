<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\FactureMail;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Evenement;
use App\Models\Kit_produit;
use App\Models\Long;
use App\Models\Mode;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\Produit_img;
use App\Models\Role;
use App\Models\Statut_transaction;
use App\Models\Stent;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Element;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProduitApiController extends Controller
{

    public function getAllProduit(){

        //$objProduits = Produit::with('categorie')->where('published','=',1)->get();
        $objProduits = DB::table('produits')
        ->join('produit_imgs','produits.id', '=','produit_imgs.produit_id')
        ->join('categories','categories.id', '=','produits.categorie_id')
        ->select(
            'produits.id as id_produit',
            'produits.designation as designation',
            'produits.description as description',
            'produits.prix_produit as prix_produit',
            'produits.ref as ref',
            'produit_imgs.name as image',
            'categories.name as categorie',
            'produits.ref as ref'
        )
        ->where('produits.categorie_id','=',1)
        ->get();

        //$kit = Kit_produit::with('produit','kit','element')->get();
        $kits = DB::table('produits')
        ->leftJoin('produit_imgs','produits.id', '=','produit_imgs.produit_id')
        ->join('categories','categories.id', '=','produits.categorie_id')
        ->select(
            'produits.id as id_produit',
            'produits.designation as designation',
            'produits.description as description',
            'produits.prix_produit as prix_produit',
            'produits.ref as ref',
            'produit_imgs.name as image',
            'categories.name as categorie',
            'produits.ref as ref'
        )
        ->where('produits.categorie_id','=',2)
        ->get();
        /*dd($kits);
        $kits = Produit::where('categorie_id','=',2)->get();*/
        $allkits = collect();
        foreach($kits as $kit){
            $elements = DB::table('kit_produits')
            ->join('elements','elements.id','=','kit_produits.element_id')
            ->where('kit_produits.kit_id','=',$kit->id_produit)
            ->select(
                'elements.id as id_element',
                'elements.name as element_name',
                'elements.ref as ref'
            )
            ->get();

            $elProd = collect();
            foreach($elements as $element){
                $prod = DB::table('kit_produits')
                ->join('produits','produits.id', '=','kit_produits.produit_id')
                ->leftJoin('produit_imgs','produits.id', '=','produit_imgs.produit_id')
                ->select(
                    'produits.id as id_produit',
                    'produits.designation as designation',
                    'produits.description as description',
                    'produits.prix_produit as prix_produit',
                    'produit_imgs.name as image',
                    'produits.ref as ref'
                )
                ->where('kit_produits.element_id','=',$element->id_element)
                ->get();

                $elProd->push([
                    'element' => $element,
                    'produits' => $prod
                ]);
            }

            $allkits->push([
                'kit' => $kit,
                'element_produit' => $elProd
            ]);
        }

        $data = [
          'list_kits' =>$kits,
          'produits' => $objProduits,
          'kits' =>$allkits
        ];

        $this->_response['message'] = 'liste des produits et kits Cardio';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);

    }

    //detail d'un produit

    public function detailProduit(Request $request){
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'produit' => 'string|required'
        ]);

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

       /* $objProduit = Produit::where('ref', '=', $request->get('produit'))->where('published','=',1)->first();
        if (empty($objProduit)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "Le produit n'existe pas!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }*/

        $objProduit = DB::table('produits')
            ->join('produit_imgs','produits.id', '=','produit_imgs.produit_id')
            ->join('categories','categories.id', '=','produits.categorie_id')
            ->select(
                'produits.id as id_produit',
                'produits.designation as designation',
                'produits.description as description',
                'produits.prix_produit as prix_produit',
                'produits.categorie_id as categorie_id',
                'produits.ref as ref',
                'produit_imgs.name as image',
                'categories.name as categorie',
                'produits.alias as alias',
                'produits.ref as ref'
            )
            ->where('produits.ref', '=',$request->get('produit'))
            ->first();
        //$kit = Produit::where('categorie_id','=',2)->where('id','=',$id)->first();
            //dd($objProduit);

            if ($objProduit->categorie_id == 2){
                $elements = DB::table('kit_produits')
                    ->join('elements','elements.id','=','kit_produits.element_id')
                    ->where('kit_produits.kit_id','=',$objProduit->id_produit)
                    ->select(
                        'elements.id as id_element',
                        'elements.name as element_name',
                        'elements.ref as ref'
                    )
                    ->groupBy('kit_produits.element_id')
                    ->get();
                //dd($elements);
                $elProd = collect();
                foreach ($elements as $element){
                    $prod = DB::table('kit_produits')
                        ->join('produits','produits.id', '=','kit_produits.produit_id')
                        ->leftJoin('produit_imgs','produits.id', '=','produit_imgs.produit_id')
                        ->select(
                            'produits.id as id_produit',
                            'produits.designation as designation',
                            'produits.description as description',
                            'produits.prix_produit as prix_produit',
                            'produit_imgs.name as image',
                            'produits.ref as ref'
                        )
                        ->where('kit_produits.element_id','=',$element->id_element)
                        ->get();

                        $elProd->push([
                            'element' => $element,
                            'produits' => $prod
                        ]);

                     /*if (in_array($element, $array)){

                     }*/


                }
                $detail = [
                    'message' => 'details du kit',
                    'produit' => $objProduit,
                    'element_produit' => $elProd
                ];
            }elseif($objProduit->alias == "orsiro-stent-coronaire-actif-hybride"){
            $long = Long::all();
            $stent = Stent::all();

            $detail = [
                'message' => 'details du kit',
                'produit' => $objProduit,
                'stent' => $stent,
                'long' => $long
            ];
        }
            else{
                $detail = [
                    'message' => 'details du produit ',
                    'produit' => $objProduit,
                ];
            }



        $this->_response['data']    = $detail;
        $this->_response['success'] = true;
        return response()->json($this->_response);
        //return response()->json($detail);
    }

    public function remainingPaymentOrder(Request $request){
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'ref_commande'=>'String|required',
            'ref_mode'=>'String|required',
            'montant'=>'String|required',
            'image'=>'String|nullable'
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
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if(!in_array($objRole->alias,array('gestionnaire'))) {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'êtes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $commande = Commande::where('ref', '=',$request->ref_commande)->with('statut_cmd')->first();
        if(empty($commande)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "Le mode de payment n'existe pas !";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        

        $mode = Mode::where('ref', '=',$request->ref_mode)->first();
        if(empty($mode)) {
            $this->_errorCode = 6;
            $this->_response['message'][] = "Le mode de payment n'existe pas !";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        

        if($commande->statut_cmd->name == 'paiement partiel'){

            $objStatutTrans = Statut_transaction::where('name','=','reussie')->first();
            $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',$objStatutTrans->id)->first();
            $commande = Commande::where('id','=',$commande->id)->first();
            
            $montantCommande = Panier::where('commande_id','=',$commande->id)->sum('prix_total');
            $restePaye = $montantCommande - intval($objTransaction->montant);
            

            if($restePaye == intval($request->get('montant'))){

                try {

                    $transaction = new Transaction();
                    $transaction->montant = $request->get('montant');
                    $transaction->total_payment = $montantCommande;
                    if($request->has('image')){
                        $image = $request->get('image');  // your base64 encoded
                        $extension = explode('/', mime_content_type($image))[1];
                        $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = $transaction->ref.'_'.Str::random(10). '.'.$extension;
                        if (Storage::disk('produit')->put($imageName, base64_decode($image))){
                            
                            $transaction->image = 'cardio-afrique/storage/app/public/images/produit/'.$imageName;
                        }
                    }
                    $transaction->commande()->associate($commande);
                    $transaction->mode()->associate($mode);
                    $transaction->statut_transaction()->associate($objStatutTrans);
                    $transaction->generateReference();
                    $transaction->generateAlias($transaction->ref);
                    $transaction->published = 1;
                    $transaction->save();

                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 7;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json( $this->_response);
                }
                //return response()->json($transaction);

                
                try {

                    $commande->update(['statut_cmd_id' => 4]);

                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 8;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json( $this->_response);
                }

                /**
                 * Instruction permettant de générer une facture
                 * */
                $transaction->downloadPDF($transaction->id);

                /*if($request->has('image')){
                    $image = $request->get('image');  // your base64 encoded

                    $extension = explode('/', mime_content_type($image))[1];

                    $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = $commande->ref.'_'.Str::random(10). '.'.$extension;

                    if (Storage::disk('produit')->put($imageName, base64_decode($image))){
                        

                        /*
                        * Mettre à jour le statut de la commande à payee
                        */

                        
                   /* }

                }else {

                    try {

                        $transaction = new Transaction();
                        $transaction->montant = $request->get('montant');
                        $transaction->total_payment = $montantCommande;
                        if($request->has('moyen')) {$transaction->moyen = $request->get('moyen');}
                        $transaction->commande()->associate($commande);
                        $transaction->mode()->associate($mode);
                        $transaction->statut_transaction()->associate($objStatutTrans);
                        $transaction->published = 1;
                        $transaction->save();

                    }catch(Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 9;
                        if (in_array($this->_env, ['local', 'development'])){
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json( $this->_response);
                    }

                

                    try {

                        $commande->update(['statut_cmd_id' => 4]);

                    }catch(Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 10;
                        if (in_array($this->_env, ['local', 'development'])){
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json( $this->_response);
                    }
                    $transaction->downloadPDF($transaction->id);
                }*/

            }else{
                DB::rollback();
                $this->_errorCode = 9;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] ="Le montant restant à payer est incorrect!";
                $this->_response['error_code']  = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }

        }

        /*if($commande->statut_cmd->name == 'en attente'){

            $objStatutTrans = Statut_transaction::where('name','=','reussie')->first();
            $objTransaction = Transaction::where('commande_id','=',$commande->id)->where('statut_trans_id','=',$objStatutTrans->id)->first();
            $commande = Commande::where('id','=',$commande->id)->first();
            if(empty($objTransaction)) {
                $montantCommande = Panier::where('commande_id','=',$commande->id)->sum('prix_total');

                if($montantCommande == intval($request->get('montant'))){
    
                    if($request->has('image')){
                        $image = $request->get('image');  // your base64 encoded
    
                        $extension = explode('/', mime_content_type($image))[1];
    
                        $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = $objTransaction->ref.'_'.Str::random(10) . '.'.$extension;
    
                        if (Storage::disk('produit')->put($imageName, base64_decode($image))){
                            try {
    
                                $transaction = new Transaction();
                                $transaction->montant = $request->get('montant');
                                $transaction->total_payment = $montantCommande;
                                $transaction->image = 'cardio-afrique/storage/app/public/images/produit/'.$imageName;
                                if($request->has('moyen')) {$transaction->moyen = $request->get('moyen');}
                                $transaction->commande()->associate($commande);
                                $transaction->mode()->associate($mode);
                                $transaction->statut_transaction()->associate($objStatutTrans);
                                $transaction->published = 1;
                                $transaction->save();
    
                            }catch(Exception $objException){
                                DB::rollback();
                                $this->_errorCode = 10;
                                if (in_array($this->_env, ['local', 'development'])){
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json( $this->_response);
                            }
    
                            try {
    
                                $commande->update(['statut_cmd_id' => 4]);
    
                            }catch(Exception $objException){
                                DB::rollback();
                                $this->_errorCode = 11;
                                if (in_array($this->_env, ['local', 'development'])){
                                }
                                $this->_response['message'] = $objException->getMessage();
                                $this->_response['error_code'] = $this->prepareErrorCode();
                                return response()->json( $this->_response);
                            }
    
                            $transaction->downloadPDF($transaction->id);
                        }
    
                    }else {
    
                        try {
    
                            $transaction = new Transaction();
                            $transaction->montant = $request->get('montant');
                            $transaction->total_payment = $montantCommande;
                            if($request->has('moyen')) {$transaction->moyen = $request->get('moyen');}
                            $transaction->commande()->associate($commande);
                            $transaction->mode()->associate($mode);
                            $transaction->statut_transaction()->associate($objStatutTrans);
                            $transaction->published = 1;
                            $transaction->save();
    
                        }catch(Exception $objException){
                            DB::rollback();
                            $this->_errorCode = 12;
                            if (in_array($this->_env, ['local', 'development'])){
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json( $this->_response);
                        }
    
                        try {
    
                            $commande->update(['statut_cmd_id' => 4]);
    
                        }catch(Exception $objException){
                            DB::rollback();
                            $this->_errorCode = 13;
                            if (in_array($this->_env, ['local', 'development'])){
                            }
                            $this->_response['message'] = $objException->getMessage();
                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json( $this->_response);
                        }
    
                        $transaction->downloadPDF($transaction->id);
                    }
    
                }else{
                    DB::rollback();
                    $this->_errorCode = 14;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] ="Le montant restant à payer est incorrect!";
                    $this->_response['error_code']  = $this->prepareErrorCode();
                    return response()->json( $this->_response );
                }
            }

        }*/

        
        $data = [
            'commande' => $commande,
            'transaction' =>$transaction
        ];
        $this->_response['message'] = 'Paiement effectué';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }


    public function createKitProduct(Request $request) {

        $this->_fnErrorCode = 1;
        $objListProduit = collect(json_decode($request->getContent(), true));
        if(empty($objListProduit)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste est vide!";
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

        //On vérifie que l'utilisateur est bien gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if (!in_array($objRole->alias,array('gestionnaire'))) {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objKit = Produit::where('ref', '=', $objListProduit['kit'])->first();
        if (empty($objKit)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "Le kit n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objElement = Element::where('ref', '=', $objListProduit['element'])->first();
        if(empty($objElement)) {
            $this->_errorCode = 6;
            $this->_response['message'][] = "L'élément n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();
        
            

        if($request->has('produits')){

            foreach ($objListProduit['produits'] as $item) {
                $objProduit = Produit::where('ref', '=', $item['produit'])->first();
                if (empty($objProduit)) {
                    $this->_errorCode = 7;
                    $this->_response['message'][] = "Le kit n'existe pas.";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                try {

                    $objKitProduit = new Kit_produit();
                    $objKitProduit->published = 1;
                    $objKitProduit->generateReference();
                    $objKitProduit->generateAlias($objProduit->designation);
                    $objKitProduit->produit()->associate($objProduit);
                    $objKitProduit->kit()->associate($objUser);
                    $objKitProduit->element()->associate($objUser);
                    $objKitProduit->save();

                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 8;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json( $this->_response );
                }
            }

        }else{
            DB::rollback();
            $this->_errorCode = 9;
            $this->_response['message'][] = "La liste de produit n'existe pas!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::commit();
        $data = [
          'kit_produit' => $objKitProduit
        ];
        $this->_response['message'] = 'kit produit créer avec success';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);

    }

    public function create(Request $request) {

        $this->_fnErrorCode = 1;
        $objListProduit = collect(json_decode($request->getContent(), true));
        if(empty($objListProduit)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        /*$validator = Validator::make($request->all(), [
            'ref_categorie' => 'string|required',
            'designation' => 'string|required',
            'description' => 'string|required',
            'prix' => 'string|required',
            'quantite' => 'string|required',
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            if (!empty($validator->errors()->all())) {
                foreach ($validator->errors()->all() as $error) {
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }*/

        $objUser = Auth::user();
        if(empty($objUser)){
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if (!in_array($objRole->alias,array('gestionnaire'))) {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objCategorie = Categorie::where('ref', '=', $objListProduit['ref_categorie'])->first();
        if(empty($objCategorie)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "La Catégorie n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();
        

        if($objListProduit->has('designation')) {

            if($objListProduit->has('prix')) {

                if($objListProduit->has('quantite')){

                    try{

                        $objProduit = new Produit();
                        $objProduit->designation = $objListProduit['designation'];
                        $objProduit->description = $objListProduit['description'];
                        $objProduit->prix_produit = $objListProduit['prix'];
                        $objProduit->qte = $objListProduit['quantite'];
                        $objProduit->published = 1;
                        $objProduit->generateReference();
                        $objProduit->generateAlias($objProduit->designation);
                        $objProduit->categorie()->associate($objCategorie);
                        $objProduit->user()->associate($objUser);
                        $objProduit->save();
            
                    }catch(Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 6;
                        if (in_array($this->_env, ['local', 'development'])){
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json( $this->_response );
                    }


                    if($request->has('images')){

                        foreach ($objListProduit['images'] as $objImg) {

                            $image = $objImg['image'];  // your base64 encoded
            
                            $extension = explode('/', mime_content_type($image))[1];
                            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                            $image = str_replace(' ', '+', $image);
                            $imageName = $objProduit->ref.'_'.Str::random(10) . '.'.$extension;
            
                            if(Storage::disk('produit')->put($imageName, base64_decode($image))){
                                try{
            
                                    $objProduitImg = new Produit_img();
                                    $objProduitImg->name = 'cardio-afrique/storage/app/public/images/produit/'.$imageName;
                                    $objProduitImg->published = 1;
                                    $objProduitImg->produit()->associate($objProduit);
                                    $objProduitImg->generateAlias($objProduit->designation);
                                    $objProduitImg->generateReference();
                                    $objProduitImg->save();
            
                                }catch(Exception $objException){
                                    DB::rollback();
                                    $this->_errorCode = 7;
                                    if (in_array($this->_env, ['local', 'development'])){
                                    }
                                    $this->_response['message'] = $objException->getMessage();
                                    $this->_response['error_code'] = $this->prepareErrorCode();
                                    return response()->json( $this->_response );
                                }
            
                            }else{
                                DB::rollback();
                                $this->_errorCode = 8;
                                if (in_array($this->_env, ['local', 'development'])){
                                }
                                $this->_response['message'] ="Echec dans l'enregistrement de image.";
                                $this->_response['error_code']  = $this->prepareErrorCode();
                                return response()->json( $this->_response );
                            }
                        }
            
                    }else{
                        DB::rollback();
                        $this->_errorCode = 9;
                        $this->_response['message'][] = "Il manque l'image du produit!";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }

                }else{
                    DB::rollback();
                    $this->_errorCode = 9;
                    $this->_response['message'][] = "Veuillez entrer une quantité!";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{
                DB::rollback();
                $this->_errorCode = 9;
                $this->_response['message'][] = "Veuillez entrer un prix du produit!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }else{
            DB::rollback();
            $this->_errorCode = 9;
            $this->_response['message'][] = "Veuillez entrer une désignation!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        
        DB::commit();
        $data = [
            'produit' => $objProduit
        ];

        $this->_response['message'] = 'produit créer avec success';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);

    }

    public function getAllCategorie(){
        $cat = Categorie::where('published','=',1)->get();

        $data = [
            'categories' => $cat,
        ];
        
        $this->_response['message']    = 'liste des catégories';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function downloadPDF($id){

        $montant = 0;
        $montant_restant_a_payer = 0;

        $objTransaction = Transaction::findorFail($id);
        //dd($montant);
        $objCommande = Commande::where('id','=',$objTransaction->commande_id)->first();
        $objCustomer = User::where('id','=',$objCommande->user_client_id)->first();

        $objPanier = Panier::where('commande_id','=',$objCommande->id)->with('produit','produit.categorie')->get();
        foreach($objPanier as $item) {
            $montant = intval($item->prix_total) + $montant;
            //$item->produit->designation
            //dd($item->produit->categorie->name);
        }

        $montant_restant_a_payer = $montant - intval($objTransaction->total_payment);

        $data = [
            'commande' => $objCommande,
            'transaction' => $objTransaction,
            'panier' => $objPanier,
            'customer' => $objCustomer,
            'reste_a_payer' => $montant_restant_a_payer
        ];

        /**Laravel-dompdf pour générer un fichier pdf */
        $view = view('facture.facture', compact('data'))->render();

        PDF::loadHTML($view)
            ->setPaper('a4', 'potrait')
            ->setWarnings(false)
            ->save(public_path().'/public/facture/facture_transaction_'.$objTransaction->id.'.pdf');

        $filename = 'facture_transaction_'.$objTransaction->id.'.pdf';

        try {

            Mail::to($objCustomer->email)
                ->send(new FactureMail($filename));
        } catch (Exception $objException) {
            //return redirect()->back()->with('error', $e->getMessage());
            DB::rollback();
            $this->_errorCode = 1;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
        }

        /**
         * return PDF::loadFile(public_path().'\project\storage\ppp\test.html')->save(public_path().'\project\storage\doc\my_stored_file.pdf')->stream('download.pdf');
         */

    }

    public function productUpdate(Request $request)
    {
        $this->_fnErrorCode = 1;
        $objListProduit = collect(json_decode($request->getContent(), true));
        if(empty($objListProduit)) {
            $this->_errorCode = 2;
            $this->_response['message'][] = "La liste est vide!";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        /*$validator = Validator::make($request->all(), [
            'produit'=>'string|required',
            'categorie'=>'string|nullable',
            'designation'=>'String|nullable',
            'description'=>'string|nullable',
            'prix_u'=>'string|nullable',
            'quantite'=>'string|nullable',
            'image'=>'nullable'
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
        }*/

        $objUser = Auth::user();
        if(empty($objUser)){
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien admin
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if ($objRole->alias != "admin" && $objRole->alias != "gestionnaire") {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objProduit = Produit::where('ref', '=', $objListProduit["produit"])->first();
        if (empty($objProduit)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "Le produit n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();

        if($objListProduit->has('designation')){

            try{

                 $objProduit->update(['designation' => $objListProduit['designation']]);
    
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 6;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }

        if($request->has('description')){

            try{
               $objProduit->update(['description' => $objListProduit['description']]);
           }catch(Exception $objException){
               DB::rollback();
               $this->_errorCode = 7;
               if (in_array($this->_env, ['local', 'development'])){
               }
               $this->_response['message'] = $objException->getMessage();
               $this->_response['error_code'] = $this->prepareErrorCode();
               return response()->json( $this->_response );
           }
            
        }

        if($objListProduit->has('prix_u')){
            try{
                $objProduit->update(['prix_produit' => $objListProduit['prix_u']]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 8;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
            
        }

        if($objListProduit->has('quantite')){
            try{
                $objProduit->update(['qte' => $objListProduit['quantite']]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 9;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }


        if($objListProduit->has('categorie')){
            $objCategorie = Categorie::where('ref', '=', $objListProduit['categorie'])->first();
            if(empty($objCategorie)) {
                $this->_errorCode = 10;
                $this->_response['message'][] = "La Categorie n'existe pas.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            try{
                $objProduit->update(['categorie_id' => $objCategorie->id]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 11;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }

        //----------------------------------------------------------------------------
        //Modification de l'image d'un produit
        //----------------------------------------------------------------------------
        if($objListProduit->has('images')){

            foreach($objListProduit['images'] as $item) {

                $objImage = Produit_img::where('ref','=',$item['image'])->first();

                $image = $item['name'];  // your base64 encoded

                $extension = explode('/', mime_content_type($image))[1];
                $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = $objProduit->ref.'_'.Str::random(10) . '.'.$extension;

                if(Storage::disk('produit')->put($imageName, base64_decode($image))){
                    try{
                        $objImage->update(['name' => 'cardio-afrique/storage/app/public/images/produit/'.$imageName]);
                    }catch(Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 12;
                        if (in_array($this->_env, ['local', 'development'])){
                        }
                        $this->_response['message'] = $objException->getMessage();
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json( $this->_response );
                    }

                }else{
                    DB::rollback();
                    $this->_errorCode = 13;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] ="Echec dans l'enregistrement de image.";
                    $this->_response['error_code']  = $this->prepareErrorCode();
                    return response()->json( $this->_response );
                }
            }

        }

        DB::commit();

        $toReturn = [
            'objet'=> $objProduit
        ];
        $this->_response['message'] = 'Modification reussi.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function kitProductUpdate(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'kit_produit'=>'string|required',
            'produit'=>'String|nullable',
            'kit'=>'string|nullable',
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
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien admin
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if ($objRole->alias != "admin" && $objRole->alias != "gestionnaire") {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objKitProduit = Kit_produit::where('ref', '=', $request->get('kit_produit'))->first();
        if (empty($objKitProduit)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "L'Ingrédient n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();

        if($request->has('produit')){
            $objProduit = Produit::where('ref', '=', $request->get('produit'))->first();
            if(empty($objProduit)) {
                DB::rollBack();
                $this->_errorCode = 6;
                $this->_response['message'][] = "L'unité n'existe pas.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            try{
                $objKitProduit->update(['produit_id' => $objProduit->id]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 7;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }

        if($request->has('kit')){
            $objKit = Produit::where('ref', '=', $request->get('kit'))->first();
            if(empty($objKit)) {
                DB::rollBack();
                $this->_errorCode = 8;
                $this->_response['message'][] = "La Categorie n'existe pas.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            try{
                $objKitProduit->update(['kit_id' => $objKit->id]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 9;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }

        DB::commit();
        $toReturn = [
            'objet'=> $objKitProduit
        ];
        $this->_response['message'] = 'Modification reussi.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function productDelete(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'produit'=>'string|required'
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
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien admin
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != 'admin' && $objRole->alias != 'gestionnaire') {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'êtes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objProduit = Produit::where('ref', '=', $request->get('produit'))->with('categorie')->first();
        if(empty($objProduit)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "Le produit n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        if($objProduit->categorie->name == 'kits') {

            $objKitProduit = Kit_produit::where('kit_id','=',$objProduit->id)->get();
            if($objKitProduit->isEmpty()) {
                try{
                    $objProduit->update(['published' => 0]);
                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 6;
                    if(in_array($this->_env, ['local', 'development'])){
                        $this->_response['message'] = $objException->getMessage();
                    }
                    $this->_response['error_code'] = $this->prepareErrorCode();
                }
            }else{

                foreach($objKitProduit as $item) {
                    try{
                        $item->update(['published' => 0]);
                    }catch(Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 7;
                        if(in_array($this->_env, ['local', 'development'])){
                            $this->_response['message'] = $objException->getMessage();
                        }
                        $this->_response['error_code'] = $this->prepareErrorCode();
                    }
                }

                try{
                    $objProduit->update(['published' => 0]);
                }catch(Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 8;
                    if(in_array($this->_env, ['local', 'development'])){
                        $this->_response['message'] = $objException->getMessage();
                    }
                    $this->_response['error_code'] = $this->prepareErrorCode();
                }
            }
            
        }

        if($objProduit->categorie->name == 'Accessoires et autres produits') {

            try{
                $objProduit->update(['published' => 0]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 9;
                if(in_array($this->_env, ['local', 'development'])){
                    $this->_response['message'] = $objException->getMessage();
                }
                $this->_response['error_code'] = $this->prepareErrorCode();
            }
            
        }

        DB::commit();

        $toReturn = [
            'objet' => $objProduit
        ];
        $this->_response['message'] = "Le produit a été supprimé!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function kitProductDelete(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'kit_produit'=>'string|required'
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
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 3;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        //On vérifie que l'utilisateur est bien admin
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if($objRole->alias != 'admin' && $objRole->alias != 'gestionnaire') {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'êtes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objKitProduit = Kit_produit::where('ref', '=', $request->get('kit_produit'))->first();
        if(empty($objKitProduit)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "Le produit n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try{
            $objKitProduit->update(['published' => 0]);
        }catch(Exception $objException){
            DB::rollback();
            $this->_errorCode = 6;
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = $objException->getMessage();
            }
            $this->_response['error_code'] = $this->prepareErrorCode();
        }

        DB::commit();
        $toReturn = [
            'objet' => $objKitProduit
        ];
        $this->_response['message'] = "Le kit produit a été supprimé!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }


}
