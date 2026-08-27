@extends('layouts.dashboard')

@section('title', 'Modifier la ville')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6"><a href="{{ route('admin.configuration.city.index') }}" class="text-primary text-xs font-medium">&larr; Retour à la liste</a><h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200 mt-2">Modifier la ville</h1></div>
        @if ($errors->any())<div class="mb-6 text-sm text-red-600">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6 md:p-8">
            <form action="{{ route('admin.configuration.city.update', $city) }}" method="POST" class="space-y-6">@csrf @method('PUT')
                <x-form.input name="name" label="Nom de la ville *" value="{{ old('name', $city->name) }}" required />
                <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-5"><x-btn href="{{ route('admin.configuration.city.index') }}" style="outline">Annuler</x-btn><x-btn type="submit">Mettre à jour</x-btn></div>
            </form>
        </div>
    </div>
@endsection
