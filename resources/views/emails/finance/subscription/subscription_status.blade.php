@component('mail::message')
Dear <b>{{ $user->first_name }}</b>,

Thank you for your active contribution to our community.

We wish to inform you that your subscription to <b>{{$wallets["receiver"]["name"]}}</b> has been <b>{{ strtolower($subscription->status) }}</b>.

@if (!empty($message))
<b style="color: red">Reason: </b> {{$message}}    
@endif

If you did not perform this action, contact support.

Thanks,<br>
Customer Care
@endcomponent
