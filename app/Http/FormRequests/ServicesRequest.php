<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
//            'cityId' => 'required',
            'lat' => 'required',
            'lon' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => __('Укажите координаты места подачи (широту)'),
            'lon.required' => __('Укажите координаты места подачи (долготу)'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
