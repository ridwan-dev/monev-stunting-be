@component('mail::message')
# Reset your Password

Silahkan klik link dibawah untuk reset password.

@component('mail::button', ['url' => 'http://stunting.id/reset-password/'. $details['token']])
Reset Password
@endcomponent

Terimakasih,<br>
{{ config('app.name') }}
@endcomponent
