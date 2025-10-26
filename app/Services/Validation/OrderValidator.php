<?php

namespace App\Services\Validation;

use App\Models\PriceHistory;
use Illuminate\Validation\ValidationException;
use Validator;

class OrderValidator
{
    public function validate(array $data): array
    {
        $validator = Validator::make(data: $data, rules: [
            'priceId' => ['required'],
            'passenger' => ['required'],
            'passenger.firstName' => ['required'],
            'passenger.phone' => ['required'],
            'passenger.email' => ['required', 'email'],
            'ticketType' => ['required', 'in:refundable,non_refundable'],
        ], messages:  [
            'priceId.required' => __('Не указан ID прайса'),
            'passenger.required' => __('Не указаны данные о пассажире'),
            'passenger.firstName.required' => __('Не указано имя пассажира'),
            'passenger.phone.required' => __('Не указан номер телефона пассажира'),
            'passenger.email.required' => __('Не указан адрес электронной почты пассажира'),
            'passenger.email.email' => __('Адрес электронной почты имеет недопустимый формат'),
            'ticketType.required' => __('Не указан тип билета'),
            'ticketType.in' => __('Тип билета имеет недопустимое значение'),
        ]);

        $priceRow = PriceHistory::query()->where('price_id', $data['priceId'])->first();

        if (!$priceRow) {
            $validator->after(function ($v) {
                $v->errors()->add('priceId', 'Указанный ID прайса не найден. Возможно, прошло слишком много времени с момента запроса цен.');
            });
        }

        $price = $priceRow->price;

        if ($price['pickupPlaceType'] === 'airport' && (!($data['pickupLocation']['flight']['flightNumber'] ?? false) || !($data['pickupLocation']['flight']['flightDateTime'] ?? false))) {
            $validator->after(function ($v) {
                $v->errors()->add('pickupLocation', 'Укажите номер и дату/время рейса');
            });
        }

        if ($price['dropoffPlaceType'] === 'airport' && (!($data['dropoffLocation']['flight']['flightNumber'] ?? false) || !($data['dropoffLocation']['flight']['flightDateTime'] ?? false))) {
            $validator->after(function ($v) {
                $v->errors()->add('dropoffLocation', 'Укажите номер и дату/время рейса');
            });
        }

        if ($price['pickupPlaceType'] === 'railway_station' && (!($data['pickupLocation']['train']['trainNumber'] ?? false) || !($data['pickupLocation']['train']['trainDateTime'] ?? false))) {
            $validator->after(function ($v) {
                $v->errors()->add('pickupLocation', 'Укажите номер и дату/время поезда');
            });
        }

        if ($price['dropoffPlaceType'] === 'railway_station' && (!($data['dropoffLocation']['train']['trainNumber'] ?? false) || !($data['dropoffLocation']['train']['trainDateTime'] ?? false))) {
            $validator->after(function ($v) {
                $v->errors()->add('dropoffLocation', 'Укажите номер и дату/время поезда');
            });
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->getData();
    }
}
