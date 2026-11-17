<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'file' => ['required', 'file', 'mimes:pdf,csv,xlsx,xls', 'max:10240'],
        ];
    }
}