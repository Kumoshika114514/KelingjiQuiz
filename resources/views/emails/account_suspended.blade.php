<x-mail::message>
@component('mail::message')
# Hello {{ $userName }},

We detected multiple failed login attempts on your account. As a security measure, your account has been temporarily suspended for **{{ $durationMinutes }} minute(s)** due to **{{ $reason }}**.

If this was you and you regained access, you can ignore this message. If you did **not** make these attempts, please consider resetting your password immediately:

@component('mail::button', ['url' => route('password.request')])
Reset your password
@endcomponent

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent
</x-mail::message>
