@extends('layouts.dashboard')

@section('content')
    <h2>Inviter un nouveau membre</h2>
    <x-container-dashed>
        <div class="py-12">
            <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
                <form method="POST" action="{{ route('admin.invitations.store') }}" class="flex flex-col gap-2">
                    @csrf
                    <x-form.input name="email" label="Adresse mail" type="email" placeholder="Entrez une adresse mail" />
                    <x-form.select name="role_id" label="Rôle" placeholder="Assigner un rôle" :options="$roles" />

                    <x-btn type="submit">Envoyer l'invitation</x-btn>
                </form>
            </div>
        </div>
    </x-container-dashed>
@endsection
