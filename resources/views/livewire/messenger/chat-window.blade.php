<div @class([
    'flex h-full min-h-0 flex-col bg-white dark:bg-gray-900',
    'fixed inset-0 z-[60] w-full md:static md:z-auto md:w-auto' => $conversation,
    'hidden md:flex' => ! $conversation,
])>
    @if(! $conversation || ! $otherUser)
        <div class="flex h-full flex-col items-center justify-center p-6 text-center">
            <i data-lucide="message-circle" class="w-12 h-12 text-gray-300 mb-3"></i>
            <p class="text-gray-500">Choisissez une conversation pour commencer</p>
        </div>
    @else
        <header class="flex shrink-0 items-center gap-3 border-b px-3 py-3 dark:border-gray-800 sm:px-4">
            <button
                type="button"
                aria-label="Retour aux conversations"
                title="Retour aux conversations"
                wire:click="closeConversation"
                class="rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white md:hidden"
            >
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
            </button>
            <x-ui.avatar :user="$otherUser" class="w-8 h-8" />
            <div class="min-w-0">
                <h3 class="truncate text-sm font-medium">{{ $otherUser->name }}</h3>
                <p class="truncate text-xs text-gray-500">{{ $conversation->contract->property->title ?? '' }}</p>
            </div>
        </header>

        <main class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-gray-50 p-3 dark:bg-gray-800/30 sm:p-4">
            @foreach($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                @endphp
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] px-4 py-2 rounded-2xl text-sm {{ $isMine ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white dark:bg-gray-700 border dark:border-gray-600 rounded-bl-sm' }}">
                        <p class="break-words">{{ $message->body }}</p>
                        <span class="text-xs {{ $isMine ? 'text-blue-100' : 'text-gray-400' }} block mt-1">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </main>

        <footer class="shrink-0 border-t bg-white p-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] dark:border-gray-800 dark:bg-gray-900">
            <div class="flex gap-2">
                <div class="min-w-0 flex-1">
                    <x-form.textarea
                        name="body"
                        rows="1"
                        placeholder="Écrire un message..."
                        class="!py-2"
                        wire:model="body"
                        wire:keydown.enter="sendMessage"
                    />
                </div>
                <button
                    type="button"
                    aria-label="Envoyer le message"
                    title="Envoyer le message"
                    wire:click="sendMessage"
                    class="self-start rounded-lg bg-primary px-3 py-2 text-white transition hover:bg-primary/90"
                >
                    <i data-lucide="send" class="h-5 w-5"></i>
                </button>
            </div>
        </footer>
    @endif
</div>