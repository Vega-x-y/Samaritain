<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelFormRequest extends FormRequest
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
            'title' => ['required', 'min:8'],
            'description' => ['nullable', 'min:8'],
            'price_per_night' => ['required', 'integer', 'min:0'],
            'price_per_hour' => ['required', 'integer', 'min:0'],
            'star_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'rooms' => ['required', 'integer', 'min:1'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'furnished' => ['nullable', 'boolean'],
            'address' => ['required', 'min:8'],
            'contact' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'min:3'],
            'city_id' => ['required', 'exists:cities,id'],
            'arrondissement_id' => ['nullable', 'exists:arrondissements,id'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'hourly_prices' => ['nullable', 'array'],
            'hourly_prices.*' => ['nullable', 'integer', 'min:0'],
            'images' => $imageRules,
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10120'],
            'cover_image' => ['nullable', 'boolean'],
            'kept_images' => ['nullable', 'array'],
            'kept_images.*' => ['integer', 'exists:hotel_images,id'],
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
