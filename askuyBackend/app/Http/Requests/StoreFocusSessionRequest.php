<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFocusSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'ambient_sound_used' => ['nullable', 'string', 'max:100'],
            'session_type' => ['required', 'in:pomodoro,short_break,long_break'],
            'stress_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}