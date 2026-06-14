@props(['project'])

<div class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300">
    <div class="relative overflow-hidden h-48">
        @if($project->image)
            <img src="{{ Storage::url($project->image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
        @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
    </div>
    <div class="p-4">
        <h4 class="font-semibold text-gray-900">{{ $project->title }}</h4>
        @if($project->description)
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $project->description }}</p>
        @endif
    </div>
</div>