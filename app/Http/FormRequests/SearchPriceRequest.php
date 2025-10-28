<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pickupLocation.name' => 'required',
            'dropoffLocation.name' => 'required',
            'pickupLocation.address' => 'required',
            'dropoffLocation.address' => 'required',
            'pickupLocation.lat' => 'required',
            'dropoffLocation.lat' => 'required',
            'pickupLocation.lon' => 'required',
            'dropoffLocation.lon' => 'required',
            'pickupAt' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'pickupLocation.name.required' => __('Не указано название места подачи'),
            'dropoffLocation.name.required' => __('Не указано название места назначения'),
            'pickupLocation.address.required' => __('Не указан адрес места подачи'),
            'dropoffLocation.address.required' => __('Не указан адрес места назначения'),
            'pickupLocation.lat.required' => __('Не указаны координаты места подачи'),
            'dropoffLocation.lat.required' => __('Не указаны координаты места назначения'),
            'pickupLocation.lon.required' => __('Не указаны координаты места подачи'),
            'dropoffLocation.lon.required' => __('Не указаны координаты места назначения'),
            'pickupAt.required' => __('Не указаны дата и время подачи'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
