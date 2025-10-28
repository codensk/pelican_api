<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServicesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cityId' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'cityId.required' => __('Не указан ID населенного пункта'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
