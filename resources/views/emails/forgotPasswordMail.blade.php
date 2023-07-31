@component('mail::message')
# Message

<p>
    Mr/Mme {{$data->name}}, vous avez oublié votre mot de passe et souhaitez avoir un nouveau,
    pour ce fait cliquer sur le lien nouveau mot de passe.
</p>

@component('mail::button', ['url' => 'https://www.edrugs-cardio.com/#/startnewpass/'.$data->ref])
Nouveau Mot De Passe
@endcomponent

<p>
    Nous vous remercions de l'interêt que vous ne cessez d'accorder à notre plateforme.
</p>

{{ config('app.name') }}

@endcomponent
