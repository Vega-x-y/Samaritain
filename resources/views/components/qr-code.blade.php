@props(['pass', 'size' => 200, 'withLabel' => false])

@php
    use App\Helpers\QrCodeHelper;
    $url = route('scan.show', $pass->uuid);
    $qrCodeDataUri = $withLabel
        ? QrCodeHelper::generate($url . '|' . $pass->holder_name, $size)
        : QrCodeHelper::generate($url, $size);
@endphp

<div {{ $attributes }}>
    <div class="bg-white p-4 rounded-lg shadow-md inline-block">
        <img src="{{ $qrCodeDataUri }}" alt="QR Code pour {{ $pass->holder_name }}"
            style="width: {{ $size }}px; height: {{ $size }}px" class="mx-auto">
        <div class="mt-4 text-center">
            <p class="text-sm font-medium text-gray-900">{{ $pass->holder_name }}</p>
            <p class="text-xs text-gray-500">UUID: {{ substr($pass->uuid, 0, 8) }}...</p>
            @if ($pass->remaining_visits > 0)
                <p class="text-xs text-green-600 mt-1">{{ $pass->remaining_visits }} visites restantes</p>
            @endif
        </div>
    </div>
</div>
