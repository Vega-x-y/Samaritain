@props(['review'])

<div class="bg-white rounded-xl border border-gray-100 p-4">
    <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden flex-shrink-0">
                @if($review->user->avatar)
                    <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->user->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-600 font-bold">
                        {{ substr($review->user->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div>
                <p class="font-medium text-gray-900">{{ $review->user->name }}</p>
                <p class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex text-amber-400">
            @for($i = 1; $i <= 5; $i++)
                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                </svg>
            @endfor
        </div>
    </div>
    
    @if($review->comment)
        <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
    @endif
</div>