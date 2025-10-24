<?php

namespace App\Http\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id ?? $this->request->get('userId');

        return [
            'name' => 'nullable|min:3',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore(id: $userId)->whereNull('deleted_at'),
            ],
            'password' => 'nullable|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => __('Минимальная длина имени - 3 символа'),
            'email.email' => __('Адрес электронной почты не соответствует формату'),
            'email.unique' => __('Указанный адрес электронной почты занят'),
            'password.min' => __('Минимальная длина пароля - 5 символа'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
