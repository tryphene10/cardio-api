@component('mail::message')
# Message

<p>Mr/Mme {{ $data['customer']->name }}, votre commande de référence *** {{ $data['commande']->ref }} *** passée, a été créée avec succès.
</p>

# Detail Commande

@component('mail::table')
| Produit                                                | Quantité                        | Prix_Unitaire                            | Prix_Total                          |
| :----------------------------------------------------- | :------------------------------ | :--------------------------------------- | :---------------------------------: |
| {{$data['product']->designation}}                      | {{ $data['panier']->quantite }} | {{ $data['product']->prix_produit }}     | {{ $data['panier']->prix_total }}   |

@endcomponent

<p style="text-align: right;">Montant Total à payer : <span style="font-weight:bold;"> #{{ $data['panier']->prix_total }} FCFA. </span>
</p>


{{ config('app.name') }}
@endcomponent
