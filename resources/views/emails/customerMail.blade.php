@component('mail::message')
<p>
        Mr/Mme {{ $data['user']->name }}

        Nous vous remercions pour vos achats et votre confiance auprès de notre plateforme CARDIO-AFRIQUE.

    Votre compte a été créé avec succès. Veuillez cliquer sur le bouton Activer votre compte pour le rendre actif.

</p>

@component('mail::button', ['url' => 'https://www.edrugs-cardio.com/#/verify/'. $data['user']->ref ])
Activer votre compte
@endcomponent

<p>
    Merci d'avoir choici notre plateforme!!!
</p>

{{ config('app.name') }}

@endcomponent
