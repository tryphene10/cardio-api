<?php

use App\Http\Controllers\Api\ProduitApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\CommandeApiController;
use App\Http\Controllers\Api\EvenementApiController;
use App\Http\Controllers\Api\ElementApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthApiController::class, 'login']);
Route::get('pdf/{id}', [ProduitApiController::class, 'downloadPDF']);

Route::post('user/detail', [UserApiController::class, 'detailUser']);

Route::get('countries/all ', [UserApiController::class, 'allCountries']);

Route::post('country/by/cities', [UserApiController::class, 'citiesByCountry']);

Route::get('customer/count', [UserApiController::class, 'CustomersCount']);

Route::get('livreurs/list', [UserApiController::class, 'allLivreurs']);

Route::get('users/list', [UserApiController::class, 'allUsers']);

Route::get('roles/list', [UserApiController::class, 'allRoles']);

Route::post('user/account/activation/{ref_use}', [UserApiController::class, 'accountActivation']);

Route::post('user/email/check', [UserApiController::class, 'forgotPassword']);
Route::post('user/password/change/{ref_user}', [UserApiController::class, 'changePassword']);


Route::get('testmailjet', [CommandeApiController::class, 'testMailJet']);
/**
 * Route to create customer
 * */

Route::post('customer/create', [UserApiController::class, 'create']);

Route::post('orange/order/check', [CommandeApiController::class, 'checkTransactionOrange']);

Route::post('mtn/order/check', [CommandeApiController::class, 'checkTransactionMtn']);

Route::post('order/detail', [CommandeApiController::class, 'orderDetail']);

/**
 * Route Produit cardio
 * */
Route::get('produit/all', [ProduitApiController::class, 'getAllProduit']);
Route::post('produit/detail', [ProduitApiController::class, 'detailProduit']);

/**
 * Route evenement
 * */
Route::get('evenement/all', [EvenementApiController::class, 'getAllEvenement']);
Route::post('evenement/detail', [EvenementApiController::class, 'detailEvenement']);

/**
 * Route categorie
 * */

Route::get('categorie/all', [ProduitApiController::class, 'getAllCategorie']);

/**
 * Route elements
 * */

Route::get('elements/all', [ElementApiController::class, 'getAllElements']);

/**
 * Route mode paiement orange et mtn
 * */

Route::get('payment/mode/list', [CommandeApiController::class, 'paymentModeList']);

/**
 * Route mode paiement par transfert, virement et cash
 * */

Route::get('payment/mode/other/list', [CommandeApiController::class, 'paymentModesOthersList']);

/**
 * Route affiche la liste des suivis
 * */

Route::get('suivis/list', [CommandeApiController::class, 'followUpOrderList']);



Route::group(['middleware'=>'auth:api'], function()
{
    Route::get('logout', [AuthApiController::class, 'logout']);

    /**
     * Route to create gestionnaire ou livreur
     * by gestionnaire or admin
     * **/
    Route::post('user/create', [UserApiController::class, 'create']);
    /**
     * Route to edit user
     * **/
    Route::post('user/update', [UserApiController::class, 'update']);


    /**
     * Route for general creation of the command by the customer
     * **/
    Route::post('customer/global-order/create', [CommandeApiController::class, 'create']);
    
    /**
     * Route for create osiro command by the customer
     * **/
    Route::post('customer/osiro/order/create', [CommandeApiController::class, 'createOrderOsiro']);

    Route::post('order/payment', [CommandeApiController::class, 'payment']);
    /**
     * Route of the customer order list
     * **/
    Route::get('customer/order/list', [CommandeApiController::class, 'customerOrderslist']);

    /**
     * Route to display order list at gestionnaire and admin
     * **/
    Route::get('order/list', [CommandeApiController::class, 'Orderslist']);

    /**
     * Route to display order pay list at deliveryman
     * **/
    Route::get('livreur/list/order/pay', [CommandeApiController::class, 'payOrderslist']);

    /**
     * Route to assign deliveryman at orders
     * **/
    Route::post('order/to/deliveryman/assign', [CommandeApiController::class, 'ordersAssignToDeliveryman']);

    /**
     * Route pour assigner un suivi à une commande (gestionnaire ou livreur)
     * **/
    Route::post('order/to/assign/suivi', [CommandeApiController::class, 'followUpOrderByUser']);

    /**
     * Route pour valider la livraison d'une commande au client par le livreur
     * **/
    Route::post('deliveryman/order/livre', [CommandeApiController::class, 'signCustomer']);

    /**
     * Route of the customer list
     * **/
    Route::get('customers/list', [UserApiController::class, 'allCustomers']);
    /**
     * Route to delete user
     * **/
    Route::get('user/delete', [UserApiController::class, 'delete']);

    /*
     * Route remaining payment
     */

    Route::post('order/payment/remaining', [ProduitApiController::class, 'remainingPaymentOrder']);

    /**
     * Route Produit cardio
     * */
    Route::post('produit/create', [ProduitApiController::class, 'create']);
    Route::post('produit/update', [ProduitApiController::class, 'productUpdate']);
    Route::post('produit/delete', [ProduitApiController::class, 'productDelete']);

    /**
     * Route Kit produit cardio
     * */
    Route::post('kit_produit/create', [ProduitApiController::class, 'createKitProduct']);
    Route::post('kit_produit/update', [ProduitApiController::class, 'kitProductUpdate']);
    Route::post('kit_produit/delete', [ProduitApiController::class, 'kitProductDelete']);

    /**
     * Route evenement
     * */
    Route::post('evenement/create', [EvenementApiController::class, 'create']);
    Route::post('evenement/update', [EvenementApiController::class, 'update']);
    Route::post('evenement/delete', [EvenementApiController::class, 'delete']);

    /**
     * Route elements
     * */
    Route::post('element/create', [ElementApiController::class, 'create']);
    Route::post('element/update', [ElementApiController::class, 'update']);
    Route::post('element/delete', [ElementApiController::class, 'delete']);

});
