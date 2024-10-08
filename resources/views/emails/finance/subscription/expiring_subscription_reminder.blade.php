@component('mail::message')
Dear {{ $user->first_name }},

We are delighted that you have been an active subscriber.

However, your subscription to {{$plan_name}} plan would be expiring on {{ $date }}.

You can subscribe to any of the plans now so you don`t miss out on enjoying our services.

@component('mail::button', ['url' => route("coperate-login-page", strtolower($user->admin_type))])
   Login
@endcomponent

Thanks,<br>
Customer Care
@endcomponent
