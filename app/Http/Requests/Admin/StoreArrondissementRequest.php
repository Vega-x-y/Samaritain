<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArrondissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('arrondissements', 'name')->where(fn ($query) => $query->where('city_id', $this->integer('city_id')))],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ];
    }
}
