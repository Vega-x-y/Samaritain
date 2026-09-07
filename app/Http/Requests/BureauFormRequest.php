<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BureauFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $imageRules = $this->isMethod('POST')
            ? ['required', 'array', 'min:1']
            : ['nullable', 'array'];

        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'min:8'],
            'price' => ['required', 'integer', 'min:0'],
            'price_type' => ['required', 'in:monthly,daily,sale'],
            'surface' => ['nullable', 'integer', 'min:1'],
            'rooms' => ['nullable', 'integer', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'arrondissement_id' => ['nullable', 'exists:arrondissements,id'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
            'images' => $imageRules,
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10120'],
            'kept_images' => ['nullable', 'array'],
            'kept_images.*' => ['integer', 'exists:bureau_images,id'],
        ];

        if ($this->isMethod('POST')) {
            $rules['conditions'] = ['required', 'accepted'];
        }

        if ($this->user() && $this->user()->isStaff()) {
            $rules['is_verify'] = ['nullable', 'boolean'];
            $rules['is_active'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}

