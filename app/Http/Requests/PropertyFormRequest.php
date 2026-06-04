<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyFormRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'min:8'],
            'description' => ['nullable', 'min:8'],
            'price' => ['required', 'integer', 'min:0'],
            'surface' => ['required', 'integer', 'min:10'],
            'rooms' => ['required', 'integer', 'min:1'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'floor' => ['required', 'integer', 'min:0'],
            'furnished' => ['nullable', 'boolean'],
            'address' => ['required', 'min:8'],
            'status' => ['nullable', 'min:3'],
            'verified' => ['nullable', 'boolean'],
        ];
    }
}
