@component('mail::message')
<p>
Cardio-afrique, vous remercie de vos achats sur sa plateforme.
Ci-dessous votre facture correspondant à votre transaction sur le produit commandé.
</p>
@component('mail::button', ['url' => 'https://api.edrugs-cardio.com/'.$filename])
Télécharger votre facture
@endcomponent
<p>
Merci d'avoir choici notre plateforme!!!
</p>
{{ config('app.name') }}
@endcomponent
