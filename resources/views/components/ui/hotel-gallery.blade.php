@props([
    'hotel' => $hotel,
])

@php
    $images = $hotel->images;
@endphp

<div class="flex flex-col gap-2 mb-8" id="hotel-gallery">
    {{-- Hero image --}}
    <div class="relative rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 group">
        @if($images->isNotEmpty())
            <img id="hotel-main-img" src="{{ $images->first()->image_url }}" alt="{{ $hotel->title }}"
                class="w-full md:h-[420px] h-64 object-cover block transition-transform duration-700 ease-[cubic-bezier(.25,.46,.45,.94)] group-hover:scale-[1.03]">
        @else
            <div class="w-full md:h-[420px] h-64 flex items-center justify-center text-gray-400">
                <i data-lucide="building-2" class="w-16 h-16"></i>
            </div>
        @endif
    </div>

    {{-- Thumbnails --}}
    @if($images->count() > 1)
        <div class="grid grid-cols-5 gap-2">
            @foreach($images as $i => $image)
                <button type="button" onclick="switchHotelImage(this, '{{ $image->image_url }}')"
                    aria-label="Image {{ $i + 1 }}"
                    class="g-thumb relative rounded-lg overflow-hidden aspect-[4/3]
                        transition-transform duration-200 hover:-translate-y-0.5
                        ring-2 ring-transparent ring-offset-0
                        {{ $i === 0 ? 'ring-primary dark:ring-primary-400' : 'hover:ring-primary dark:hover:ring-primary-400' }}">
                    <img src="{{ $image->image_url }}" alt="" loading="{{ $i > 0 ? 'lazy' : 'eager' }}"
                        class="w-full h-full object-cover block">
                </button>
            @endforeach
        </div>
    @endif
</div>

<script>
    function switchHotelImage(thumb, src) {
        const img = document.getElementById('hotel-main-img');
        img.style.opacity = '0';
        img.style.transition = 'opacity .2s';
        setTimeout(() => {
            img.src = src;
            img.style.opacity = '1';
        }, 180);
        document.querySelectorAll('.g-thumb').forEach(t => {
            t.classList.remove('ring-primary', 'dark:ring-primary-400');
            t.classList.add('ring-transparent');
        });
        thumb.classList.remove('ring-transparent');
        thumb.classList.add('ring-primary', 'dark:ring-primary-400');
    }

    document.querySelectorAll('.g-thumb').forEach(thumb => {
        thumb.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                thumb.click();
            }
        });
    });
</script>
