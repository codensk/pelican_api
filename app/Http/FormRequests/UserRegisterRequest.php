<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'password' => 'required|min:5',
            'role' => 'required|in:client,user',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Укажите имя'),
            'name.min' => __('Минимальная длина имени - 3 символа'),
            'email.required' => __('Укажите адрес электронной'),
            'email.email' => __('Адрес электронной почты не соответствует формату'),
            'email.unique' => __('Указанный адрес электронной почты занят'),
            'password.required' => __('Укажите пароль'),
            'password.min' => __('Минимальная длина пароля - 5 символа'),
            'role.required' => __('Укажите роль'),
            'role.in' => __('Допустимые значения роли: user, client'),

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
