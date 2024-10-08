@component('mail::message')

Hello <b>{{$model->name}}!</b><br>

You requested to update your parishioner details.<br>

<b>CODE</b>: {{$pin->code}} <i>(Expires in {{ $expires_at }})<i><br><br>

If you did not request for this update, kindly ignore this message, no further action is required.<br>
Regards.
@endcomponent
