@component('mail::message')
Dear <b>{{ $model->full_name }}</b>,

We are thrilled to inform you that your subscription to <b>{{ $plan->name }}</b> was successful. With this subscription, you now have access to exclusive features and benefits tailored to enhance your experience with us.

As a valued member of our community, your support is invaluable to us. We are committed to providing you with exceptional service and ensuring your satisfaction every step of the way.

If you have any questions, concerns, or feedback regarding your subscription or any aspect of our services, please do not hesitate to reach out to our dedicated support team.
We are here to assist you and address any inquiries you may have.

Once again, thank you for choosing to be part of our community. We look forward to continuing to serve you and providing you with an unparalleled experience.

Best regards,

Customer Care
@endcomponent
