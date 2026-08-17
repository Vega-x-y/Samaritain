@extends('layouts.dashboard')

@section('title', 'QR Code | Samaritain')

@section('content')
<div class="p-6">
    <div class="max-w-xl">
        <h1 class="text-xl font-semibold mb-1">QR Code du site</h1>
        <p class="text-sm text-gray-500 mb-6">
            Génère un QR code pointant vers la page d'accueil du site. Utile pour l'imprimer sur des supports physiques (flyers, affiches, cartes de visite).
        </p>

        <div class="border rounded-lg p-6 bg-white shadow-sm">

            @if(session('success'))
                <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-md px-3 py-2">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
                    {{ session('error') }}
                </div>
            @endif

            @if($qrExists)
                <img src="{{ asset('storage/qrcodes/site.png') }}?v={{ time() }}"
                     alt="QR Code du site"
                     class="w-48 h-48 mb-5 border rounded-lg p-2">
            @else
                <div class="w-48 h-48 mb-5 border border-dashed rounded-lg flex items-center justify-center text-gray-400 text-sm">
                    Aucun QR code généré
                </div>
            @endif

            <div class="flex gap-3">
                <form action="{{ route('admin.qrcode.generate') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg text-sm font-medium hover:bg-orange-700 transition-colors">
                        {{ $qrExists ? 'Regénérer le QR code' : 'Générer le QR code' }}
                    </button>
                </form>

                @if($qrExists)
                    <a href="{{ route('admin.qrcode.download') }}"
                       class="px-4 py-2 border border-orange-600 text-orange-600 rounded-lg text-sm font-medium hover:bg-orange-50 transition-colors">
                        Télécharger
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection