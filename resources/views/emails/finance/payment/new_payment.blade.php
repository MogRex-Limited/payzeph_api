Dear <b>{{ $user->full_name }}</b>,

We are happy to inform you that your payment of <b>{{ format_monwey($transaction->amount) }}</b> for the purchase of {{ $transaction->unit_quantity }} was successful.

If you have any questions, concerns, or feedback regarding your payment or any aspect of our services, please do not hesitate to reach out to our dedicated support team.
We are here to assist you and address any inquiries you may have.

Best regards,

Customer Care
@endcomponent
