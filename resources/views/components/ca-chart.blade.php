@props(['data', 'labels'])

@php
    $maxValue = max($data);
    $maxValue = $maxValue > 0 ? $maxValue : 1;
@endphp

<div class="space-y-3">
    @foreach($labels as $index => $label)
        @php
            $value = $data[$index];
            $percentage = ($value / $maxValue) * 100;
            $percentage = max($percentage, 2); // Minimum 2% pour la visibilité
        @endphp
        
        <div class="flex items-center gap-4">
            <div class="w-24 text-sm text-gray-600 font-medium">{{ $label }}</div>
            <div class="flex-1 bg-gray-100 rounded-lg h-10 relative overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-lg flex items-center justify-end pr-3 transition-all duration-500"
                     style="width: {{ $percentage }}%">
                    @if($percentage > 25)
                        <span class="text-white text-sm font-semibold">{{ number_format($value, 0, ',', ' ') }} €</span>
                    @endif
                </div>
            </div>
            @if($percentage <= 25)
                <div class="w-24 text-right text-sm text-gray-700 font-medium">
                    {{ number_format($value, 0, ',', ' ') }} €
                </div>
            @endif
        </div>
    @endforeach
</div>