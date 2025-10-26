<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPlaceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'search.required' => __('Не указан поисковый запрос'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
