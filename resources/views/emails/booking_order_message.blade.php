<x-mail::message>
# Здравствуйте!

Заказ {{ $orderData['orderId'] }}.

{{ $messageText }}

Ссылка на детали о заказе: <a href="{{ route("order.show", ['orderId' => $orderData['code']]) }}">Перейти</a>

С уважением,<br/>
{{ config('app.name') }}
</x-mail::message>
