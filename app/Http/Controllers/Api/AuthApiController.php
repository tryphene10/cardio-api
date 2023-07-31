<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] expires_at
     */

    public function login(Request $request)
    {

        $this->_fnErrorCode = "01";
        $validator = Validator::make($request->all(), [
            'email'=>'required|email|max:'.Config::get('constants.size.max.email').'|exists:users,email',
            'password'=>'required|min:'.Config::get('constants.size.min.password').'|max:'.Config::get('constants.size.max.password')

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


        $objUser = User::where('email', $request->get('email'))
            ->first();
        if(empty($objUser) || !$objUser->isPublished())
        {
            $this->_errorCode               = 3;
            $this->_response['message'][]   = trans('auth.denied');
            $this->_response['error_code']   = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        $arrPasswordValidation = $objUser->validatePassword($request->get('password'));
        if($arrPasswordValidation['success'] == false)
        {
            $this->_errorCode               = 4;
            $this->_response['message'][]   = trans('messages.login.fail.default');
            $this->_response['error_code']   = $this->prepareErrorCode();
            return response()->json($this->_response);
        }

        try
        {
            $objToken = $objUser->createToken('PersonalAccessToken');
        }
        catch(Exception $objException)
        {

            $this->_errorCode             = 5;
            if(in_array($this->_env, ['local', 'development']))
            {
                $this->_response['message'][]   = $objException->getMessage();
            }
            $this->_response['message'][]   = trans('messages.token.fail.generate');
            $this->_response['error_code']   = $this->prepareErrorCode();
            return response()->json($this->_response);
        }


        $toReturn = [
            'token'=>$objToken->accessToken,
            'ref_connected_user'=>$objUser->ref,
            'token_type'=>'Bearer',
            'infos' => $objUser
        ];
        $this->_response['data']    = $toReturn;
        $this->_response['success'] = true;

        return response()->json($this->_response);
    }





    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $objUser            = Auth::user();
        $this->_fnErrorCode = "01";

        if(empty($objUser))
        {
            $this->_errorCode = 2;
            $this->_response['error_code'] = $this->prepareErrorCode();
            $this->_response['message'][]   = Lang::get('messages.error-occured.default');
            return response()->json($this->_response);

        }

        $request->user()->token()->revoke();

        $arrResult[] = [
            'message'=>Lang::get('logged-out')
        ];
        $this->_response['success'] = true;
        $this->_response['data'] = [
            'result'=>$arrResult
        ];

        return response()->json($this->_response);
    }
}
