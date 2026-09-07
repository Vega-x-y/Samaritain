@props([])

<div class="h-[calc(100dvh-10rem)] min-h-0 md:h-[calc(100dvh-9rem)]">
    <div class="flex h-full min-h-0 overflow-hidden rounded-lg border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="h-full min-h-0 w-full border-r dark:border-gray-800 md:w-80 md:shrink-0">
            <livewire:messenger.conversation-list />
        </div>
        <div class="hidden h-full min-h-0 flex-1 md:block">
            <livewire:messenger.chat-window />
        </div>
    </div>
</div>