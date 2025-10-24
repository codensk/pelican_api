<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email'
            ],
            'password' => 'required|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Укажите адрес электронной'),
            'email.email' => __('Адрес электронной почты не соответствует формату'),
            'password.required' => __('Укажите пароль'),
            'password.min' => __('Минимальная длина пароля - 5 символа'),

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
