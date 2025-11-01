<x-mail::message>
# Здравствуйте!

Ваш заказ {{ $orderId }} успешно оплачен.

Ссылка на детали о заказе: <a href="{{ route("order.show", ['orderId' => $orderCode]) }}">Перейти</a>

С уважением,<br/>
{{ config('app.name') }}
</x-mail::message>
