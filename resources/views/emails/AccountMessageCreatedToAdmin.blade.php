@component('mail::message')
# Message

<p>
    Un compte client vient d'être créé par Mr/Mme {{ $data['user']->name }} sur la plateforme CARDIO-AFRIQUE. 
</p>

{{ config('app.name') }}
@endcomponent
