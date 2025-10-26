<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'priceId' => 'required',
            'passenger' => 'required',
            'ticketType' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'priceId.required' => __('Не указан ID прайса'),
            'passenger.required' => __('Не указаны данные о пассажире'),
            'ticketType.required' => __('Не указан тип билета'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
