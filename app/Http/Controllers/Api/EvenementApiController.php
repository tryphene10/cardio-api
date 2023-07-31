<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evenement;
use App\Models\Role;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EvenementApiController extends Controller
{

    public function getAllEvenement(){

        $evenement = Evenement::with('user')->where('published','=',1)->get();

        $futureEvent = collect();
        $historicEvent = collect();
        $currentDate = Carbon::now();
        foreach($evenement as $item) {
            /*$beginDate = new DateTime();
            $end = new DateTime();*/
            if((strtotime($item->begin) > strtotime($currentDate)) || (strtotime($item->end) > strtotime($currentDate))){
                $futureEvent->push($item);
            }else{
                $historicEvent->push($item);
            }
            //dd($item->begin);
        }

        $data = [
            'evenement-future' => $futureEvent,
            'evenements-historique' => $historicEvent,
        ];

        $this->_response['message'] = 'liste des évènements';
        $this->_response['data']    = $data;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function create(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'titre'=>'String|required',
            'begin'=>'String|required',
            'end'=>'String|required',
            'url_image'=>'String|nullable',
            'description'=>'String|nullable',
            'lieu_evenement'=>'String|required'
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
        if ($objRole->alias != "admin" && $objRole->alias != "gestionnaire") {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        // Start transaction!
        DB::beginTransaction();

        try{

            $objEvenement = new Evenement();
            $objEvenement->titre = $request->get('titre');
            $objEvenement->begin = new DateTime($request->get('begin'));
            $objEvenement->end = new DateTime($request->get('end'));
            if($request->has('description')){$objEvenement->description = $request->get('description');}
            $objEvenement->lieu_evenement = $request->get('lieu_evenement');
            $objEvenement->generateReference();
            $objEvenement->generateAlias($objEvenement->titre);
            $objEvenement->user()->associate($objUser);
            if($request->has('url_image')){
                $image = $request->get('url_image');  // your base64 encoded

                $extension = explode('/', mime_content_type($request->get('url_image')))[1];

                $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = $objEvenement->ref.'_'.Str::random(10).'.'.$extension;

                if(Storage::disk('evenement')->put($imageName, base64_decode($image))){

                    $objEvenement->url_image = 'cardio-afrique/storage/app/public/images/evenement/'.$imageName ;
                    $objEvenement->published = 1;
                    $objEvenement->save();
                }else{
                    DB::rollback();
                    $this->_errorCode = 5;
                    if (in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] ="Echec dans l'enregistrement de image.";
                    $this->_response['error_code']  = $this->prepareErrorCode();
                    return response()->json( $this->_response );
                }

            }else{
                DB::rollback();
                $this->_errorCode = 6;
                $this->_response['message'][] = "Veuillez entrer une image!";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }catch(Exception $objException){
            DB::rollback();
            $this->_errorCode = 7;
            if (in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = $objException->getMessage();
            }
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        DB::commit();

        $toReturn = [
            'objet'=> $objEvenement
        ];

        $this->_response['message'] = 'Enregistrement reussi.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function update(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'ref_evenement'=>'String|required',
            'titre'=>'String|nullable',
            'begin'=>'String|nullable',
            'end'=>'String|nullable',
            'url_image'=>'String|nullable',
            'description'=>'String|nullable',
            'lieu_evenement'=>'String|nullable'
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

        //On vérifie que l'utilisateur est bien admin|gestionnaire
        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if ($objRole->alias != "admin" && $objRole->alias != "gestionnaire") {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'étes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objEvenement = Evenement::where('ref', '=', $request->get("ref_evenement"))->first();
        if (empty($objEvenement)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "L'évènement n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::beginTransaction();

        if($request->has('titre')){
            try{
                $objEvenement->update(['titre' => $request->get('titre')]);
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
                $objEvenement->update(['description' => $request->get('description')]);
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

        if($request->has('lieu_evenement')){
            try{
                $objEvenement->update(['lieu_evenement' => $request->get('lieu_evenement')]);
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

        if($request->has('begin')){
            try{
                $objEvenement->update(['begin' => new DateTime($request->get('begin'))]);
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

        if($request->has('end')){
            try{
                $objEvenement->update(['end' => new DateTime($request->get('end'))]);
            }catch(Exception $objException){
                DB::rollback();
                $this->_errorCode = 10;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }

        //----------------------------------------------------------------------------
        //Modification de l'image de l'igrédient
        //----------------------------------------------------------------------------
        if($request->has('url_image')){

            $image = $request->get('url_image');  // your base64 encoded
            $extension = explode('/', mime_content_type($request->get('url_image')))[1];

            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = $objEvenement->ref.'_'.Str::random(10).'.'.$extension;

            if (Storage::disk('evenement')->put($imageName, base64_decode($image))){
                $objEvenement->update(['url_image' => 'cardio-afrique/storage/app/public/images/evenement/'.$imageName]);
            }else{
                DB::rollback();
                $this->_errorCode = 11;
                if (in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = "Echec de la modificaton de l'image.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }

        }

        DB::commit();

        $toReturn = [
            'objet'=> $objEvenement
        ];

        $this->_response['message'] = 'Modification reussi.';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function delete(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'ref_evenement'=>'string|required'
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

        $objEvenement = Evenement::where('ref', '=', $request->get('ref_evenement'))->first();
        if (empty($objEvenement)) {
            $this->_errorCode = 5;
            $this->_response['message'][] = "L'évènement n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        try{
            $objEvenement->update(['published' => 0]);
        }catch(Exception $objException){
            DB::rollback();
            $this->_errorCode = 6;
            if (in_array($this->_env, ['local', 'development'])){
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        DB::commit();
        $toReturn = [
            'objet' => $objEvenement
        ];

        $this->_response['message'] = "L'évènement a été supprimé!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function detailEvenement(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'ref_evenement'=>'string|required'
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

        $objEvenement = Evenement::where('ref', '=', $request->get('ref_evenement'))->first();
        if(empty($objEvenement)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "L'évènement n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::commit();
        $toReturn = [
            'objet' => $objEvenement
        ];

        $this->_response['message'] = "Détail d'un évènement!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }
}
