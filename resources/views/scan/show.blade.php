@extends('layouts.base')

@section('title', 'Scan du Pass')

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if ($result['valid'])
                <div class="bg-green-100 border border-green-400 text-green-700 p-6 rounded-lg text-center">
                    <h2 class="text-2xl font-bold mb-4">✓ Pass Valide</h2>
                    <div class="text-left max-w-md mx-auto">
                        <p><strong>Titulaire:</strong> {{ $result['pass']->holder_name }}</p>
                        <p><strong>Expiration:</strong> {{ $result['pass']->expiration_date->format('d/m/Y') }}</p>
                        <p><strong>Visites restantes:</strong>
                            {{ $result['pass']->remaining_visits }}/{{ $result['pass']->allowed_visits }}</p>
                    </div>
                    <form action="{{ route('scan.process') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="uuid" value="{{ $result['pass']->uuid }}">
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-6 rounded">
                            Valider la visite
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-red-100 border border-red-400 text-red-700 p-6 rounded-lg text-center">
                    <h2 class="text-2xl font-bold mb-4">✗ {{ $result['message'] }}</h2>
                    @if ($result['pass'])
                        <p><strong>Titulaire:</strong> {{ $result['pass']->holder_name }}</p>
                    @endif
                    <a href="{{ route('scan.index') }}"
                        class="inline-block mt-6 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Nouveau scan
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
