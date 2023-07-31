@component('mail::message')
# Message

<p>
    La commande de référence *** {{ $data['commande']->ref }} *** vient d'être passée par Mr/Mme {{$data['customer']->name}}.
</p>


# Detail Commande

@component('mail::table')
| Produit                                                 | Quantité                        | Prix_Unitaire                           | Prix_Total                         |
| :------------------------------------------------------ | :------------------------------ |:--------------------------------------- | :--------------------------------: |
| {{ $data['product']->designation}}                      | {{ $data['panier']->quantite }} | {{ $data['product']->prix_produit }}    | {{ $data['panier']->prix_total }}  |

@endcomponent

<p style="text-align: right;">Montant Total à payer : <span style="font-weight:bold;"> #{{ $data['panier']->prix_total }} FCFA. </span>
</p>

{{ config('app.name') }}
@endcomponent
