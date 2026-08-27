<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArrondissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-settings') ?? false;
    }

    public function rules(): array
    {
        $arrondissement = $this->route('arrondissement');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('arrondissements', 'name')
                    ->where(fn ($query) => $query->where('city_id', $this->integer('city_id')))
                    ->ignore($arrondissement->id),
            ],
            'city_id' => [
                'required',
                'integer',
                'exists:cities,id',
            ],
        ];
    }
}
