<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdditionalStopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lat' => 'required',
            'lon' => 'required',
            'pickupLat' => 'required',
            'pickupLon' => 'required',
            'dropoffLat' => 'required',
            'dropoffLon' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => __('Укажите широту доп. адреса'),
            'lon.required' => __('Укажите долготу доп. адреса'),
            'pickupLat.required' => __('Укажите широту места подачи'),
            'pickupLon.required' => __('Укажите долготу места подачи'),
            'dropoffLat.required' => __('Укажите широту места назначения'),
            'dropoffLon.required' => __('Укажите долготу места назначения'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
