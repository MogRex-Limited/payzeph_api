@component('mail::message')
Dear {{ $user->first_name }},

Thank you for your active contribution to our earning community.

We wish to inform you that your subscription to {{$plan_name}} plan has expired.

You can subscribe to any of the plans now so you don`t miss out on enjoying our services.

@component('mail::button', ['url' => route("coperate-login-page", strtolower($user->admin_type))])
   Login
@endcomponent

Thanks,<br>
Customer Care
@endcomponent
