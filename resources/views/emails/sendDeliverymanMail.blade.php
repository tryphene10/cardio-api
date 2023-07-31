@component('mail::message')
# Message

<p>
Mr {{$data->name}}, tu viens d'être assigné à des commandes.
Clique sur le lien ci-dessous pour y consulter.
</p>

@component('mail::button', ['url' => 'https://www.edrugs-cardio.com/#/livreur'])
Accéder Au Dashboard
@endcomponent

<p>
Cordialement.
</p>

{{ config('app.name') }}

@endcomponent
