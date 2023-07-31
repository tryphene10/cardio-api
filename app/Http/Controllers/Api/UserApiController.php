<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountMessageCreatedToAdmin;
use App\Mail\customerMail;
use App\Mail\ForgotPasswordMail;
use App\Models\Pays;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Ville;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Mail;

class UserApiController extends Controller
{
    /**Create user */
    public function create(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'prenom' => 'nullable',
            'email' => 'required|email|max:255',
            'phone' => 'required',
            'ville' => 'nullable',
            'quartier' => 'nullable',
            'role' => 'nullable',
            'password'=>'required|min:'.Config::get('constants.size.min.password').'|max:'.Config::get('constants.size.max.password'),
        ]);

        if ($validator->fails())
        {
            if (!empty($validator->errors()->all()))
            {
                foreach ($validator->errors()->all() as $error)
                {
                    $this->_response['message'][] = $error;
                }
            }
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objUser = User::where('email', '=', $request->get('email'))->first();
        if(!empty($objUser))
        {
            $this->_errorCode               = 3;
            $this->_response['message'][]   = "Le mail est dèjà utilisé";
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objUserPhone = User::where('phone', '=', $request->get('phone'))->first();
        if(!empty($objUserPhone)){
            $this->_errorCode = 4;
            $this->_response['message'][] = "Le numero de téléphone existe déjà.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objUser = Auth::user();
        //$reponse = "";
        $mail_reponse = "";

        // Start transaction!
        DB::beginTransaction();

        if(empty($objUser)) {

            if(!$request->has('role')){

                $objRole = Role::where('alias', '=', 'client')->first();
                if(empty($objRole)){
                    $this->_errorCode = 5;
                    $this->_response['message'][] = "Le rôle 'Client' n'existe pas";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                $objVille = Ville::where('id', '=', intval($request->get('ville')))->first();
                if(empty($objVille)){
                    $this->_errorCode = 6;
                    $this->_response['message'][] = "La ville n'existe pas";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }  

                try{

                    $objUser = new User();
                    $objUser->name = $request->get('nom');
                    if($request->has('prenom')){$objUser->surname = $request->get('prenom');}
                    $objUser->email = $request->get('email');
                    $objUser->password = Hash::make($request->get('password'));
                    $objUser->phone = $request->get('phone');
                    if($request->has('quartier')){
                        $objUser->quartier = $request->get('quartier');
                    }else {
                        $this->_errorCode = 7;
                        $this->_response['message'][] = "Veuillez entrer un quartier.";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }
                    $objUser->published = 0;
                    if($request->has('ville')){
                        $objUser->ville()->associate($objVille);
                    }else {
                        $this->_errorCode = 8;
                        $this->_response['message'][] = "Veuillez entrer une ville.";
                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json($this->_response);
                    }
                    $objUser->generateReference();
                    $objUser->generateAlias($request->get('nom'));
                    $objUser->generateCode();
                    $objUser->role()->associate($objRole);
                    $objUser->save();

                }catch (Exception $objException){
                    DB::rollback();
                    $this->_errorCode = 9;
                    if(in_array($this->_env, ['local', 'development'])){
                    }
                    $this->_response['message'] = $objException->getMessage();

                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json( $this->_response );
                }

                $data = [
                    'user' => $objUser
                ];

                //dd($data);

                try {
                   
                    Mail::to($objUser->email)->send(new customerMail($data));
        
                    $mail_reponse = 'Email has been sent to '. $objUser->email;
                }catch (Exception $objException) {

                    DB::rollBack();
                    $this->_errorCode = 10;
                    if(in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }


                $objUserAdmin = User::where('role_id','=',1)->first();
                try {
                    Mail::to($objUserAdmin->email)->send(new AccountMessageCreatedToAdmin($data));
                } catch (Exception $objException) {
                    DB::rollBack();
                    $this->_errorCode = 11;
                    if(in_array($this->_env, ['local', 'development'])) {
                    }
                    $this->_response['message'] = $objException->getMessage();
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }
                
            }else {
                $this->_errorCode = 12;
                $this->_response['message'][] = "Ce champ ne doit pas prendre de valeur.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }

        if(!empty($objUser)){

            $objAuthRole = Role::where('id', '=', $objUser->role_id)->first();
            if(empty($objAuthRole)){
                $this->_errorCode = 13;
                $this->_response['message'][] = "L'utilisateur n'a pas de rôle.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            if($request->has('role')){

                $objRole = Role::where('ref', '=', $request->get('role'))->first();
                if(empty($objRole)){
                    $this->_errorCode = 14;
                    $this->_response['message'][] = "Aucun rôle n'a été renseigné.";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

                if(in_array($objAuthRole->alias, array('admin'))) {

                    if(in_array($objRole->alias, array('gestionnaire','livreur'))) {
                        
                        $user_connect = $objUser;
                        try{

                            $objUser = new User();
                            $objUser->name = $request->get('nom');
                            if($request->has('prenom')) {
                                $objUser->surname = $request->get('prenom');
                            }
                            $objUser->phone = $request->get('phone');
                            $objUser->email = $request->get('email');
                            $objUser->password = Hash::make($request->get('password'));
                            $objUser->published = 1;
                            $objUser->generateReference();
                            $objUser->generateAlias($request->get('nom'));
                            //$objUser->generateCode();
                            $objUser->role()->associate($objRole);
                            $objUser->user()->associate($user_connect);
                            $objUser->save();

                        }catch (Exception $objException) {
                            DB::rollback();
                            $this->_errorCode = 15;
                            if (in_array($this->_env, ['local', 'development'])) {
                            }
                            $this->_response['message'] = $objException->getMessage();

                            $this->_response['error_code'] = $this->prepareErrorCode();
                            return response()->json($this->_response);
                        }

                    }
                    
                }elseif(in_array($objAuthRole->alias, array('gestionnaire'))){

                    $user_connect = $objUser;
                    try {

                        $objUser = new User();
                        $objUser->name = $request->get('nom');
                        if($request->has("prenom")){$objUser->surname = $request->get('prenom');}
                        $objUser->phone = $request->get('phone');
                        $objUser->email = $request->get('email');
                        $objUser->password = Hash::make($request->get('password'));
                        $objUser->published = 1;
                        $objUser->generateReference();
                        $objUser->generateAlias($request->get('nom'));
                        //$objUser->generateCode();
                        $objUser->user()->associate($user_connect);
                        $objUser->role()->associate($objRole);
                        $objUser->save();

                    }catch (Exception $objException){
                        DB::rollback();
                        $this->_errorCode = 16;
                        if(in_array($this->_env, ['local', 'development'])){
                        }
                        $this->_response['message'] = $objException->getMessage();

                        $this->_response['error_code'] = $this->prepareErrorCode();
                        return response()->json( $this->_response );
                    }

                }else{
                    $this->_errorCode = 17;
                    $this->_response['message'][] = "Vous n'ètes pas habilité.";
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }
            }
        }

        // Commit the queries!
        DB::commit();
        //Format d'affichage de message
        $toReturn = [
            'mail_reponse' => $mail_reponse,
            'objet' => $objUser
        ];

        $this->_response['message'] = 'Votre compte a été créé avec succès!';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Detail user
    public function detailUser(Request $request){
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_user' => 'required'
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

        $objuser = User::where('ref', $request->get('ref_user'))->first();
        if(empty($objuser)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "L'utilisateur n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try {

            /*$objDetailUser = DB::table('users')
                ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
                ->select('roles.name as role',
                    'roles.ref as role_ref',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.surname as user_surname',
                    'users.phone as user_phone',
                    'users.email as user_email',
                    'users.ville as user_ville',
                    'users.quartier as user_quartier',
                    'users.ref as user_ref')
                ->where("users.ref", "=", $objuser->ref)
                ->get();*/

            $objDetailUser = User::where('ref','=',$objuser->ref)->with('role','ville')->first();

        } catch (Exception $objException) {
            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        DB::commit();
        $toReturn = [
            'objet' => $objDetailUser
        ];

        $this->_response['message'] = "Detail de l'utilisateur ";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    //Liste des pays
    public function allCountries()
	{
		$this->_fnErrorCode = 1;

		try {
            
			$objPays = Pays::where('published','=',1)->get();

		}catch (Exception $objException) {
			$this->_errorCode = 2;
			if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json( $this->_response );
		}

        $listePays = collect();
        foreach($objPays as $pays){
            
            $listePays->push($pays);

        }

		$toReturn = [
			'pays' => $listePays
		];
		$this->_response['data'] = $toReturn;
		$this->_response['success'] = true;
		return response()->json($this->_response);
	}

	//Liste des villes d'un pays
	public function citiesByCountry(Request $request)
	{
		$this->_fnErrorCode = 1;
		$validator = Validator::make($request->all(), [
			'id_pay'=>'String|required'
		]);

		if ($validator->fails())
		{
			if (!empty($validator->errors()->all())) {
				foreach ($validator->errors()->all() as $error) {
					$this->_response['message'][] = $error;
				}
			}
			$this->_errorCode = 2;
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

        //Récupération de l'objet pays
		$objPays = Pays::where('id', '=', intval($request->get('id_pay')))->first();
		if(empty($objPays)) {
			$this->_errorCode = 3;
			$this->_response['message'][] = "Le pays n'existe pas";
			$this->_response['error_code'] = $this->prepareErrorCode();
			return response()->json($this->_response);
		}

		try {

			$objRegion = Region::where('regions.published','=',1)
			            ->where('regions.pay_id','=',$objPays->id)
			            ->get();
			
			$allVilles = collect();
			foreach($objRegion as $item) {
				/*$objVille = DB::table('villes')
				->join('regions','regions.id','=','villes.region_id')
				->select('villes.id as id_ville','villes.name as name')
				->where('villes.published','=',1)
				->where('villes.region_id','=',$item->id)
				->orderBy('villes.id','desc')
				->get();*/

                $objVilles = Ville::where('region_id','=',$item->id)->where('published','=',1)->orderBy('id','desc')->get();
                foreach ($objVilles as $objVille) {
                    $allVilles->push($objVille);
                }
				// $allVilles->push(array(
				// 	'region'=>$item,
				// 	'villes'=>$objVille
				// ));

                
			}

		}catch (Exception $objException){
			$this->_errorCode = 4;
			if(in_array($this->_env, ['local', 'development'])) {
				$this->_response['message'] = $objException->getMessage();
			}
			$this->_response['error_code']  = $this->prepareErrorCode();
			return response()->json( $this->_response );
		}

		$toReturn = [
			'ville'=> $allVilles
		];
		$this->_response['message'] = 'Liste des villes par pays';
		$this->_response['data'] = $toReturn;
		$this->_response['success'] = true;
		return response()->json($this->_response);
	}

    public function allCustomers()
    {
        $this->_fnErrorCode = 1;

        $objUser = Auth::user();
        if(empty($objUser)){
            if(in_array($this->_env, ['local', 'development'])){
                $this->_response['message'] = 'Cette action nécéssite une connexion.';
            }

            $this->_errorCode = 2;
            $this->_response['error_code']  = $this->prepareErrorCode();
            return response()->json( $this->_response );
        }

        $objRole = Role::where('alias', '=', 'client')->first();
        if(empty($objRole)){
            $this->_errorCode = 3;
            $this->_response['message'][] = "Le rôle client n'exite pas";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try {

            $objCustomers = User::where('role_id', '=', $objRole->id)->with('role')->get();

        } catch (Exception $objException) {

            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);

        }

        $toReturn = [
            'objet' => $objCustomers
        ];

        $this->_response['message'] = "Liste de tous les clients.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function CustomersCount()
    {
        $this->_fnErrorCode = 1;

        $objRole = Role::where('alias', '=', 'client')->first();
        if(empty($objRole)){
            $this->_errorCode = 2;
            $this->_response['message'][] = "Le rôle client n'existe pas";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try {

            $objCountUsers = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->select(DB::raw('count(users.id) as nb_customer'))
            ->where('users.role_id', '=', $objRole->id)
            ->groupBy('users.role_id')
            ->get();

        } catch (Exception $objException) {
            $this->_errorCode = 3;
            if (in_array($this->_env, ['local', 'development'])) {
                $this->_response['message'] = $objException->getMessage();
            }
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $toReturn = [
            'objet' => $objCountUsers
        ];

        $this->_response['message'] = "Nombre d'utilisateurs clients.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    // liste des livreurs
    public function allLivreurs(){
        $this->_fnErrorCode = 1;

        try {

            $allLivreurs = User::where('role_id', '=', 4)->get();
            
        }catch (Exception $objException) {
            $this->_errorCode = 3;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $toReturn = [
            'objet' => $allLivreurs
        ];

        $this->_response['message'] = "Liste des livreurs.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    // liste des utilisateurs
    public function allUsers(){
        $this->_fnErrorCode = 1;

        try{

            $allUsers = User::with('role','ville')->get();
            
        }catch(Exception $objException) {
            $this->_errorCode = 3;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $toReturn = [
            'objet' => $allUsers
        ];

        $this->_response['message'] = "Liste des utilisateurs.";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    // update user
    public function update(Request $request)
    {
        $this->_fnErrorCode = 1;
        $validator = Validator::make($request->all(), [
            'ref_user'=>'string|required',
            'nom'=>'nullable',
            'prenom'=>'nullable',
            'phone'=>'nullable',
            'quartier'=>'nullable',
            'ville'=>'nullable',
            'email'=>'nullable|email|max:255',
            'role'=>'nullable',
            'password'=>'nullable|min:'.Config::get('constants.size.min.password').'|max:'.Config::get('constants.size.max.password'),
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
            $this->_response['message'][] = "Cette action nécéssite une connexion.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objAuthRole = Role::where("id", $objUser->role_id)->first();
        if(empty($objAuthRole)){
            DB::rollback();
            $this->_errorCode = 4;
            $this->_response['message'][] = "Le user n'a pas de rôle.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        // Start transaction!
        DB::beginTransaction();

        $objUpdateUser = User::where('ref', '=', $request->get('ref_user'))->first();
        if(empty($objUpdateUser)){
            DB::rollback();
            $this->_errorCode = 5;
            $this->_response['message'][] = "L'utilisateur n'existe pas";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        if($request->has('nom') && $request->get('nom')!=""){

            try {
                $objUpdateUser->update(["name" => $request->get('nom')]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 6;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }

        if($request->has('prenom') && $request->get('prenom')!=""){

            try {
                $objUpdateUser->update(["surname" => $request->get('prenom')]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 7;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }
        if($request->has('phone') && $request->get('phone')!=""){

            try {
                $objUpdateUser->update(["phone" => $request->get('phone')]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 8;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }

        if($request->has('ville') && $request->get('ville')!=""){

            try {
                $objUpdateUser->update(["ville_id" => intval($request->get('ville'))]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 9;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }
        }

        if($request->has('quartier') && $request->get('quartier')!=""){
            try {
                $objUpdateUser->update(["quartier" => $request->get('quartier')]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 10;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }
        }


        if($request->has('password') && $request->get('password')!=""){

            try {
                $objUpdateUser->update(["password" => Hash::make($request->get('password'))]);
    
            } catch (Exception $objException) {
                DB::rollback();
                $this->_errorCode = 11;
                if (in_array($this->_env, ['local', 'development'])) {
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }
        }

        if($request->has('role') && $request->get('role')!=""){
            $objUserRole = Role::where('ref', '=', $request->get('role'))->first();
            if(empty($objUserRole)){
                DB::rollback();
                $this->_errorCode = 12;
                $this->_response['message'][] = "Le 'Rôle' n'existe pas";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

            try{
                $objUpdateUser->update(["role_id" => $objUserRole->id]);
            }catch (Exception $objException){
                DB::rollback();
                $this->_errorCode = 13;
                if(in_array($this->_env, ['local', 'development'])){
                }
                $this->_response['message'] = $objException->getMessage();
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json( $this->_response );
            }
        }


        // Commit the queries!
        DB::commit();

        //Format d'affichage de message
        $toReturn = [
            'objet' => $objUpdateUser
        ];

        $this->_response['message'] = 'Modification réussi!';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function delete(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_user'=>'required'
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
            $this->_response['message'][] = "Cette action nécéssite une connexion.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objAuthRole = Role::where('id', '=', $objUser->role_id)->first();
        if(empty($objAuthRole)){
            $this->_errorCode = 4;
            $this->_response['message'][] = "L'utilisateur n'a pas de rôle.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $auth_01 = array("admin","gestionnaire");
        if($request->has("ref_user")){

            if(in_array($objAuthRole->alias, $auth_01)){

                $objDelUser = User::where("ref", $request->get("ref_user"))->first();

                try {

                    $objDelUser->update(["published" => 0]);
        
                } catch (Exception $objException) {
                    $this->_errorCode = 5;
                    if (in_array($this->_env, ['local', 'development'])) {
                        $this->_response['message'] = $objException->getMessage();
                    }
                    $this->_response['error_code'] = $this->prepareErrorCode();
                    return response()->json($this->_response);
                }

            }else{
                DB::rollback();
                $this->_errorCode = 6;
                $this->_response['message'][] = "Vous n'étes pas habilié.";
                $this->_response['error_code'] = $this->prepareErrorCode();
                return response()->json($this->_response);
            }

        }else{
            $this->_errorCode = 7;
            $this->_response['message'][] = "User n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }
        $toReturn = [
            'objet' => $objDelUser
        ];

        $this->_response['message'] = "L'utilisateur a été supprimé!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }


    public function accountActivation(Request $request)
    {
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref_user'=>'required'
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

        $objUser = User::where('ref','=',$request->get("ref_user"))->first();
        if(empty($objUser)){
            $this->_errorCode = 3;
            $this->_response['message'][] = "L'objet 'User' n'existe pas.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try {

            $objUser->update(['published' => 1]);

        } catch (Exception $objException) {
            $this->_errorCode = 4;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $toReturn = [
            'objet' => $objUser
        ];

        $this->_response['message'] = "Votre compte a été activé!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

    public function forgotPassword(Request $request){
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'email'=>'email|required'
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

        $objUser = User::where('email', $request->get('email'))->first();
        if(empty($objUser)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "utilisateur inconnu !";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try {
            Mail::to($objUser->email)->send(new ForgotPasswordMail($objUser));
            $mail_reponse = 'Email a été envoyé au client '.$objUser->name;
        }catch (Exception $objException) {
            DB::rollBack();
            $this->_errorCode = 4;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        $toReturn = [
            'mail_reponse' => $mail_reponse,
            'objet' => $objUser
        ];
        $this->_response['message'] = 'Vérifier votre boite mail';
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);

    }

    public function changePassword(Request $request){
        $this->_fnErrorCode = 1;

        $validator = Validator::make($request->all(), [
            'ref'=>'string|required',
            'password'=>'string|required'
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

        $objUser = User::where('ref', $request->get('ref'))->first();
        if(empty($objUser)) {
            $this->_errorCode = 3;
            $this->_response['message'][] = "utilisateur inconnu !";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $objRole = Role::where('id', '=', $objUser->role_id)->first();
        if(empty($objRole)) {
            $this->_errorCode = 4;
            $this->_response['message'][] = "Vous n'êtes pas habilité à réaliser cette tâche.";
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $password = Hash::make($request->get('password'));

        try{
            $objUser->update(['password' => $password]);
        }
        catch(Exception $objException) {
            $this->_errorCode = 5;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'][] = $objException->getMessage();
            $this->_response['message'][] = trans('messages.token.fail.generate');
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try{
            $objToken = $objUser->createToken('PersonalAccessToken');
        }catch(Exception $objException) {
            $this->_errorCode = 6;
            if(in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'][] = $objException->getMessage();
            $this->_response['message'][] = trans('messages.token.fail.generate');
            $this->_response['error_code'] = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $toReturn = [
            'token'=>$objToken->accessToken,
            'ref_connected_user'=>$objUser->ref,
            'objet' => $objUser
        ];

        $this->_response['message'] = "Le nouveau mot de passe vient d'être créé. Vous pouvez vous connecter!";
        $this->_response['data'] = $toReturn;
        $this->_response['success'] = true;
        return response()->json($this->_response);
    }

     // Afficher la liste des rôles
     public function allRoles()
     {
         $this->_errorCode = 1;
         $toReturn = [
             'message' => Role::all(),
         ];
         $this->_response['data'] = $toReturn;
         $this->_response['success'] = true;
         return response()->json($this->_response);
     }

}
