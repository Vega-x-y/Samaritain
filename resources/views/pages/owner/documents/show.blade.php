@extends('layouts.owner')

@section('title', $document->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- En-tête -->
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('owner.documents.index') }}"
           class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $document->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $document->category_label }}
                @if($document->property)
                    · {{ $document->property->title }}
                @endif
            </p>
        </div>
    </div>

    <!-- Carte du document -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php
                        $catIcons = ['invoice' => 'receipt', 'receipt' => 'check-square', 'quote' => 'file-edit', 'inspection' => 'clipboard-check', 'other' => 'paperclip'];
                        $catColors = ['invoice' => 'red', 'receipt' => 'emerald', 'quote' => 'blue', 'inspection' => 'purple', 'other' => 'gray'];
                        $icon = $catIcons[$document->category] ?? 'paperclip';
                        $color = $catColors[$document->category] ?? 'gray';
                    @endphp
                    <div class="w-10 h-10 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 rounded-xl flex items-center justify-center">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5 text-{{ $color }}-600 dark:text-{{ $color }}-400"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ number_format($document->file_size / 1024, 1) }} Ko
                    </span>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    Ajouté le {{ $document->created_at->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <!-- Affichage du fichier -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Aperçu du document</h3>
                @if(str_starts_with($document->mime_type, 'image/'))
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <img src="{{ $document->url }}" alt="{{ $document->name }}"
                             class="w-full h-auto object-contain" />
                    </div>
                @elseif($document->mime_type === 'application/pdf')
                    <iframe src="{{ $document->url }}"
                            class="w-full h-[600px] border border-gray-200 dark:border-gray-700 rounded-lg"
                            title="{{ $document->name }}"></iframe>
                @else
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center">
                        <i data-lucide="file-text" class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Ce type de fichier ne peut pas être affiché en ligne.
                        </p>
                        <a href="{{ $document->url }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Voir le document original
                        </a>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('owner.documents.download', $document) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Télécharger
                </a>

                <form action="{{ route('owner.documents.destroy', $document) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce document ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush