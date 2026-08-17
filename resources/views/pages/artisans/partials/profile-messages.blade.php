<div id="profile-message-thread" class="space-y-3">
    @forelse ($conversation?->messages ?? collect() as $message)
        <div class="flex {{ $message->expediteur_type === 'client' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[75%] {{ $message->expediteur_type === 'client' ? 'bg-primary text-primary-foreground' : 'bg-muted/70 text-foreground' }} rounded-2xl px-4 py-2.5">
                <div class="text-xs font-medium mb-1 {{ $message->expediteur_type === 'client' ? 'text-primary-foreground/80' : 'text-muted-foreground' }}">
                    {{ $message->expediteur_nom }}
                </div>

                @if ($message->contenu)
                    <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                @endif

                @if ($message->document_id && $message->document && $message->document->isDevis())
                    @if (! $message->document->isSigned())
                        <div class="mt-3 pt-2 border-t {{ $message->expediteur_type === 'client' ? 'border-white/20' : 'border-border' }}">
                            <a href="{{ route('client.documents.show', $message->document) }}"
                                class="inline-flex items-center gap-2 bg-card text-foreground border border-border px-4 py-2 rounded-lg text-sm font-medium transition hover:bg-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Consulter le devis
                            </a>
                        </div>
                    @else
                        <div class="mt-3 pt-2 border-t {{ $message->expediteur_type === 'client' ? 'border-white/20' : 'border-border' }}">
                            <span class="inline-flex items-center gap-2 bg-success/10 text-success px-4 py-2 rounded-lg text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Devis accepté le {{ $message->document->signed_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    @endif
                @elseif ($message->fichier_path)
                    <div class="mt-2 {{ $message->contenu ? 'pt-2 border-t ' . ($message->expediteur_type === 'client' ? 'border-white/20' : 'border-border') : '' }}">
                        <a href="{{ Storage::disk('r2')->url($message->fichier_path) }}" target="_blank"
                            class="flex items-center gap-2 text-sm underline {{ $message->expediteur_type === 'client' ? 'text-primary-foreground/90 hover:text-primary-foreground' : 'text-primary hover:text-primary/80' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>{{ $message->fichier_nom }}</span>
                            <span class="text-xs opacity-75">({{ number_format(($message->fichier_taille ?? 0) / 1024, 1) }} Ko)</span>
                        </a>
                    </div>
                @endif

                <div class="text-xs mt-1 flex items-center gap-1 {{ $message->expediteur_type === 'client' ? 'text-primary-foreground/70' : 'text-muted-foreground' }}">
                    {{ $message->created_at?->format('d/m/Y H:i') }}
                    @if ($message->lu)
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-muted-foreground text-sm">
            Aucun message pour le moment. Envoyez votre premier message à cet artisan !
        </div>
    @endforelse
</div>
