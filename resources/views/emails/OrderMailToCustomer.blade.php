@component('mail::message')
# Message

<p>Mr/Mme {{ $data['customer']->name }}, votre commande de référence *** {{ $data['commande']->ref }} *** passée, a été créée avec succès.
</p>

# Detail Commande

@component('mail::table')
| Produit                              | Quantité                           | Prix_Unitaire                        | Prix_Total                           |
| :----------------------------------- | :--------------------------------- | :----------------------------------- | :----------------------------------: |
@foreach ($data['products'] as $product)
| {{$product['product']->designation}} | {{ $product['panier']->quantite }} | {{ $product['product_unit_price'] }} | {{ $product['panier']->prix_total }} |
@endforeach
@endcomponent

<p style="text-align: right;">Montant Total à payer : <span style="font-weight:bold;"> #{{ $data['montant_commande'] }} FCFA. </span></p>

<p>Nous vous remercions de l'interêt que vous ne cessez d'accorder à nos produits.</p>

{{ config('app.name') }}

@endcomponent
