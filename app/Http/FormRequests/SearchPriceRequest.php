<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pickupLocation' => 'required',
            'dropoffLocation' => 'required',
            'pickupAt' => 'required',
            'pickupLat' => 'required',
            'pickupLon' => 'required',
            'dropoffLat' => 'required',
            'dropoffLon' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'pickupLocation.required' => __('Не указано место подачи'),
            'dropoffLocation.required' => __('Не указано место назначения'),
            'pickupAt.required' => __('Не указаны дата и время подачи'),
            'pickupLat.required' => __('Не указаны координаты точки подачи (lat)'),
            'pickupLon.required' => __('Не указаны координаты точки подачи (lon)'),
            'dropoffLat.required' => __('Не указаны координаты точки назначения (lat)'),
            'dropoffLon.required' => __('Не указаны координаты точки назначения (lon)'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
