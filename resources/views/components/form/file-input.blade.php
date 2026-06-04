@props([
    'label' => '',
    'name',
])

<div>
    @if($label)
        <label
            for="{{ $name }}"
            class="block text-xs font-medium text-gray-700 mb-1"
        >
            {{ $label }}
        </label>
    @endif

    <input
        type="file"
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300 px-4 py-2
            file:mr-4 file:rounded-md file:border-0
            file:bg-primary file:px-4 file:py-2
            file:text-white hover:file:opacity-90'
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>