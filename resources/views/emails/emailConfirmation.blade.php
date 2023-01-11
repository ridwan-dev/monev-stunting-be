@component('mail::message')
# Konfirmasi Email Anda

Silahkan klik link dibawah ini untuk konfirmasi email anda.
Akun tidak akan aktif jika email tidak dapat dikonfirmasi.

@component('mail::button', ['url' => 'http://stunting.id/'.$details['hash'].'/?email='.$details['email']])
Konfirmasi Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
