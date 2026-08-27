<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-settings') ?? false;
    }

    public function rules(): array
    {
        $city = $this->route('city');

        return ['name' => ['required', 'string', 'max:255', 'unique:cities,name,'.$city->id]];
    }
}
