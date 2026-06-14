<section class="max-w-7xl mx-auto px-6 py-16">

    <div class="text-center mb-10">
        <p class="text-primary text-sm font-semibold font-display uppercase tracking-widest mb-2">Brazzaville</p>
        <h2 class="text-2xl md:text-3xl font-bold font-display text-gray-900">Quartiers populaires</h2>
        <p class="text-gray-500 mt-2 text-sm max-w-md mx-auto">
            Explorez les zones les plus recherchées de la ville.
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">

        @foreach ([
            ['name' => 'Bacongo',       'count' => 14, 'icon' => 'building-2'],
            ['name' => 'Moungali',      'count' => 9,  'icon' => 'building-2'],
            ['name' => 'Poto-Poto',     'count' => 11, 'icon' => 'store'],
            ['name' => 'Ouenzé',        'count' => 7,  'icon' => 'home'],
            ['name' => 'Talangaï',      'count' => 6,  'icon' => 'home'],
            ['name' => 'Makélékélé',    'count' => 8,  'icon' => 'building'],
            ['name' => 'Centre-ville',  'count' => 5,  'icon' => 'landmark'],
            ['name' => 'Djiri',         'count' => 4,  'icon' => 'trees'],
        ] as $district)
            <a href="{{ route('property.index', ['district' => $district['name']]) }}"
                class="group flex flex-col gap-3 p-5 rounded-2xl border border-accent hover:border-primary/30 hover:shadow-sm transition-all duration-200">

                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <i data-lucide="{{ $district['icon'] }}" class="w-5 h-5 text-primary"></i>
                </div>

                <div>
                    <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $district['name'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $district['count'] }} bien{{ $district['count'] > 1 ? 's' : '' }} disponible{{ $district['count'] > 1 ? 's' : '' }}</p>
                </div>

                <div class="flex items-center gap-1 text-xs text-primary font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                    Voir les biens
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </div>

            </a>
        @endforeach

    </div>

</section>