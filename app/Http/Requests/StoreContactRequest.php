<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:500'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Veuillez indiquer votre nom.',
            'email.required' => 'Veuillez indiquer votre adresse email.',
            'email.email' => 'Veuillez indiquer une adresse email valide.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'subject.required' => 'Veuillez indiquer un sujet.',
            'subject.max' => 'Le sujet ne doit pas dépasser 500 caractères.',
            'message.required' => 'Veuillez écrire votre message.',
            'message.max' => 'Votre message ne doit pas dépasser 5000 caractères.',
        ];
    }
}
