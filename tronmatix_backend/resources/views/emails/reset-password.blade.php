@component('mail::message')
<div style="text-align:center; margin-bottom: 24px;">
    <img src="{{ asset('logo.png') }}" alt="Tronmatix Computer" style="height:48px;">
</div>

# Reset Your Password

Hello!

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Reset Password
@endcomponent

This password reset link will expire in **60 minutes**.

If you did not request a password reset, no further action is required.

Thanks,<br>
**{{ config('app.name') }}**

@slot('subcopy')
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: [{{ $url }}]({{ $url }})
@endslot
@endcomponent
