@props([
    'title',
    'value',
    'color' => 'gray',
    'format' => 'number'
])

@php
    $colorClasses = [
        'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300',
        'green' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-300',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-300',
        'red' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-300',
        'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300',
        'gray' => 'bg-gray-100 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400',
    ];

    $iconColors = [
        'blue' => 'text-blue-600 dark:text-blue-300',
        'green' => 'text-green-600 dark:text-green-300',
        'yellow' => 'text-yellow-600 dark:text-yellow-300',
        'red' => 'text-red-600 dark:text-red-300',
        'purple' => 'text-purple-600 dark:text-purple-300',
        'gray' => 'text-gray-600 dark:text-gray-400',
    ];

    $valueColors = [
        'blue' => 'text-foreground',
        'green' => 'text-green-600 dark:text-green-300',
        'yellow' => 'text-yellow-600 dark:text-yellow-300',
        'red' => 'text-red-600 dark:text-red-300',
        'purple' => 'text-purple-600 dark:text-purple-300',
        'gray' => 'text-foreground',
    ];

    $colorClass = $colorClasses[$color] ?? $colorClasses['gray'];
    $iconColor = $iconColors[$color] ?? $iconColors['gray'];
    $valueColor = $valueColors[$color] ?? $valueColors['gray'];

    $formattedValue = match($format) {
        'currency' => number_format($value, 0, ',', ' ') . ' FCFA',
        'decimal' => number_format($value, 1, ',', ' '),
        default => number_format($value, 0, ',', ' '),
    };
@endphp

<div class="bg-card rounded-lg shadow-sm border border-border p-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-muted-foreground">{{ $title }}</p>
            <p class="text-3xl font-bold {{ $valueColor }} mt-2">{{ $formattedValue }}</p>
        </div>
        <div class="p-3 {{ $colorClass }} {{ $iconColor }} rounded-lg">
            {{ $icon }}
        </div>
    </div>
</div>