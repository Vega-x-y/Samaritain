<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ContactAgencyRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'message'   => ['required', 'string', 'max:5000'],
            // Honeypot : champ caché qui doit rester vide
            'website'   => ['sometimes', 'string', 'max:255', 'in:'],
            // Protection contre les soumissions trop rapides (timestamp)
            'time'      => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Messages de validation personnalisés.
     */
    public function messages(): array
    {
        return [
            'name.required'      => 'Votre nom est obligatoire.',
            'name.max'           => 'Le nom ne doit pas dépasser 100 caractères.',
            'email.required'     => 'Votre adresse email est obligatoire.',
            'email.email'        => 'Veuillez saisir une adresse email valide.',
            'email.max'          => 'L\'email ne doit pas dépasser 255 caractères.',
            'phone.regex'        => 'Le numéro de téléphone n\'est pas valide.',
            'message.required'   => 'Le message ne peut pas être vide.',
            'message.max'        => 'Le message ne doit pas dépasser 5000 caractères.',
            'website.in'         => 'Une erreur inattendue s\'est produite.',
            'time.required'      => 'Une erreur inattendue s\'est produite.',
            'time.integer'       => 'Une erreur inattendue s\'est produite.',
        ];
    }

    /**
     * Prépare les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyage des champs
        $this->merge([
            'name'    => trim($this->name),
            'email'   => strtolower(trim($this->email)),
            'phone'   => $this->phone ? trim($this->phone) : null,
            'message' => trim($this->message),
        ]);

        // Ajout du timestamp pour la protection
        if (! $this->has('time')) {
            $this->merge(['time' => time()]);
        }
    }

    /**
     * Validation après les règles : vérifier le délai minimum entre deux soumissions.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $time = $this->input('time');
            // Si la soumission est faite en moins de 3 secondes, considérer comme spam
            if ($time && (time() - $time) < 3) {
                $validator->errors()->add('time', 'Veuillez patienter avant de soumettre à nouveau.');
            }
        });
    }
}