<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}