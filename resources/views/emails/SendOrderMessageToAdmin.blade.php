@component('mail::message')
# Message

<p>
    La commande de référence *** {{ $data['commande']->ref }} *** vient d'être passée par Mr/Mme {{$data['customer']->name}}.
</p>


## Detail Commande

@component('mail::table')
| Produit                              | Quantité                           | Prix_Unitaire                         | Prix_Total                           |
| :----------------------------------- | :--------------------------------- | :------------------------------------ | :----------------------------------: |
@foreach ($data['products'] as $product)
| {{$product['product']->designation}} | {{ $product['panier']->quantite }} | {{ $product['product_unit_price'] }}  | {{ $product['panier']->prix_total }} |
@endforeach
@endcomponent

<p style="text-align: right;">Montant Total à payer : <span style="font-weight:bold;"> #{{ $data['montant_commande'] }} FCFA. </span>
</p>

{{ config('app.name') }}
@endcomponent
