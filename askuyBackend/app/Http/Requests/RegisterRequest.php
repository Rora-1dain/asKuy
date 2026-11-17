<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'education_level' => ['required', 'string', Rule::in([
                'sma',
                'smk',
                'sarjana',
                'magister',
                'prefer_not_to_say',
            ])],
            'major' => [
                'nullable',
                'string',
                'max:150',
                Rule::requiredIf(fn (): bool => in_array($this->input('education_level'), [
                    'smk',
                    'sarjana',
                    'magister',
                ], true)),
            ],
            'study_goal' => ['nullable', 'string', 'max:255'],
        ];
    }
}