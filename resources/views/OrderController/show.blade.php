@extends("layouts.order")

@section("content")
    <div class="container py-5 order-page">
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary">✈️ Pelican Transfer {{ $order->orderId }}</h1>
            <p class="text-muted">Ваучер подтверждения бронирования трансфера</p>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="card-title text-secondary mb-3">📄 Детали поездки</h5>
                <div class="row">
                    <div class="col-md-4"><strong>Номер заказа:</strong> {{ $order->orderId }}</div>
                    <div class="col-md-4"><strong>Дата бронирования:</strong> {{ $order->createdAt->format('d.m.Y') }}</div>
                    <div class="col-md-4"><strong>Класс:</strong> {{ $order->getCarClassName() }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="card-title text-secondary mb-3">🚘 Маршрут</h5>
                <div class="route">
                    <div class="route-point">
                        <span class="label">Откуда:</span>
                        <p>{{ $order->getPickupLocation() }}</p>
                    </div>
                    <div class="route-arrow">⬇️</div>
                    <div class="route-point">
                        <span class="label">Куда:</span>
                        <p>{{ $order->getDropoffLocation() }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Дата подачи:</strong> {{ $order->getPickupDate() }}<br>
                    <strong>Время подачи:</strong> {{ $order->getPickupTime() }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="card-title text-secondary mb-3">👤 Пассажир</h5>
                <p><strong>Имя:</strong> {{ $order->getPassengerName() }}</p>
                <p><strong>Количество пассажиров:</strong> {{ $order->getNumberOfPassengers() }}</p>
                <p><strong>Телефон:</strong> {{ $order->getPassengerPhone() }}</p>
                <p><strong>Email:</strong> {{ $order->getPassengerEmail() }}</p>
                <p><strong>Комментарии:</strong> {{ $order->getDriverComment() }}</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="card-title text-secondary mb-3">💰 Стоимость</h5>
                <table class="table table-striped align-middle table-bordered">
                    <thead>
                    <tr>
                        <th>Услуга</th>
                        <th class="text-end">Стоимость (₽)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($order->getServicePrices() as $service)
                        <tr>
                            <td>{{ $service['serviceName'] }}</td>
                            <td class="text-end">{{ number_format($service['servicePrice'], 2, ".", " ") }} ₽</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold border-top-2">
                        <td class="border-top-2">Итого</td>
                        <td class="text-end border-top-2">{{ number_format($order->prices->fullPrice, 2, ".", " ") }} ₽</td>
                    </tr>
                    <tr class="text-success fw-bold"><td>Возвратный</td><td class="text-end">{{ $order->isRefundable ? number_format($order->prices->fullPriceRefundable, 2, ".", " ") : "-" }} ₽</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <h5 class="card-title text-secondary mb-3">ℹ️ Информация о встрече</h5>
                <p>Если у вас есть вопросы о том, как будет происходить встреча, вы можете ознакомиться с инструкцией по ссылке:</p>
                <a href="https://pelican.online/meet" class="btn btn-outline-primary text-decoration-none" target="_blank">Перейти к инструкции</a>
            </div>
        </div>

        <div class="text-center mt-5">
            <p class="text-success fw-bold fs-5">🟢 Спасибо, что выбрали Pelican!</p>
            <p>Желаем вам приятной поездки.</p>
        </div>
    </div>
@endsection
