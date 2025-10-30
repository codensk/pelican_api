<x-mail::message>
# Здравствуйте!

Заказ {{ $orderData['orderId'] }}.

{{ $messageText }}

С уважением,<br/>
{{ config('app.name') }}
</x-mail::message>
