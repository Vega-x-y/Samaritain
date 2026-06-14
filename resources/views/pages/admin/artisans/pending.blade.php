@extends('layouts.dashboard')

@section('content')
    <div class="bg-white border-b border-gray-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900">Artisans en attente de validation</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($artisans as $artisan)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0">
                            @if ($artisan->avatar)
                                <img src="{{ Storage::url($artisan->avatar) }}" alt="{{ $artisan->business_name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xl font-bold">
                                    {{ substr($artisan->business_name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $artisan->business_name }}</h3>
                            <p class="text-sm text-blue-600">{{ $artisan->profession }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $artisan->city }}</p>
                            <p class="text-xs text-gray-400 mt-2">Inscrit {{ $artisan->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @if ($artisan->categories->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($artisan->categories as $category)
                                    <span
                                        class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full text-xs">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="text-xs text-gray-500">
                            <p>{{ $artisan->phone }}</p>
                            <p>{{ $artisan->user->email }}</p>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('admin.artisans.show', $artisan) }}"
                            class="flex-1 text-center bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                            Voir détails
                        </a>
                        <form action="{{ route('admin.artisans.verify', $artisan) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="w-full bg-emerald-600 text-white py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                                Valider
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-600">Aucun artisan en attente</h3>
                    <p class="text-gray-400">Tous les artisans ont été traités</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $artisans->links() }}
        </div>
    </div>
@endsection
