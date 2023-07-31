<?php

namespace App\Models;

use App\Mail\FactureMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CustFunc;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Exception;

class Transaction extends Model
{
    use HasFactory;
    public $fillable = [
        'taspay_transaction',
        'statut_trans_id'
    ];

    public function commande(){
        return $this->belongsTo('App\Models\Commande','commande_id');
    }

    public function statut_transaction(){
        return $this->belongsTo('App\Models\Statut_transaction','statut_trans_id');
    }

    public function mode(){
        return $this->belongsTo('App\Models\Mode','mode_id');
    }

    public function generateReference(){

        if(empty($this->attributes['ref'])){
            do{
                $token = CustFunc::getToken(Config::get('constants.values.reference'));
            }
            while (Transaction::where('ref',$token)->first() instanceof Transaction);
            $this->attributes['ref'] = $token;

            return true;
        }
        return false;
    }

    //To generate an alias for the object based on the name of that object.
    public function generateAlias($name){
        $append = Config::get('constants.values.zero');
        if(empty($this->attributes['alias'])){
            do{
                if($append == Config::get('constants.values.zero')){
                    $alias = CustFunc::toAscii($name);
                }else{
                    $alias = CustFunc::toAscii($name)."-".$append;
                }
                $append += Config::get('constants.values.one');
            }while(Transaction::where('alias',$alias)->first() instanceof Transaction);
            $this->attributes['alias'] = $alias;
        }
    }

    //pdf
    public function downloadPDF($id){

        $montant = 0;
        $montant_restant_a_payer = 0;

        $objTransaction = Transaction::findorFail($id);
        $objCommande = Commande::where('id','=',$objTransaction->commande_id)->first();
        $objCustomer = User::where('id','=',$objCommande->user_client_id)->first();

        $objPanier = Panier::where('commande_id','=',$objCommande->id)->with('produit','produit.categorie')->get();

        $collDetail = collect();
        foreach($objPanier as $item) {
            $montant = intval($item->prix_total) + $montant;

            try {

                $objDetailPanier = Detail_panier::where('panier_id','=',$item->id)->with('produit','long_stent','long_stent.long','long_stent.stent')->get();

            }catch(Exception $objException) {
                $this->_fnErrorCode = 1;
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

            //$item->produit->designation
            //dd($item->produit->categorie->name);
        }

        $montant_restant_a_payer = $montant - intval($objTransaction->total_payment);

        $data = [
            'commande' => $objCommande,
            'transaction' => $objTransaction,
            'all_paniers' => $collDetail,
            'montant_a_payer_panier' => $montant,
            'customer' => $objCustomer,
            'reste_a_payer' => $montant_restant_a_payer
        ];

        /**Laravel-dompdf pour générer un fichier pdf */
        $view = view('facture.facture', compact('data'))->render();

        PDF::loadHTML($view)
            ->setPaper('a4', 'potrait')
            ->setWarnings(false)
            ->save('facture_transaction_'.$objTransaction->id.'.pdf');//public_path().

        $filename = 'facture_transaction_'.$objTransaction->id.'.pdf';

        try{

            Mail::to($objCustomer->email)
            ->send(new FactureMail($filename));

        } catch (Exception $objException) {
            //return redirect()->back()->with('error', $e->getMessage());
            DB::rollback();
            $this->_errorCode = 2;
            if (in_array($this->_env, ['local', 'development'])) {
            }
            $this->_response['message'] = $objException->getMessage();
            $this->_response['error_code'] = $this->prepareErrorCode();
        }

        /**
         * return PDF::loadFile(public_path().'\project\storage\ppp\test.html')->save(public_path().'\project\storage\doc\my_stored_file.pdf')->stream('download.pdf');
         */

    }

}
