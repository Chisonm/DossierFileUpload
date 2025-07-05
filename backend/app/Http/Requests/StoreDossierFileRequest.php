<?php

namespace App\Http\Requests;

use App\Models\DossierFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDossierFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,png,jpg,jpeg',
                'max:4096', // 4MB in kilobytes
            ],
            'file_type' => [
                'required',
                'string',
                Rule::in(DossierFile::FILE_TYPES),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required',
            'file.file' => 'The uploaded file is not valid',
            'file.mimes' => 'The file must be a PDF, PNG, or JPG',
            'file.max' => 'The file size must not exceed 4MB',
            'file_type.required' => 'The file type is required',
            'file_type.in' => 'The file type must be one of: '.implode(', ', DossierFile::FILE_TYPES),
        ];
    }
}
