<?php

namespace App\Livewire\Messenger;

use App\Models\Contract;
use App\Models\OwnerConversation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ConversationList extends Component
{
    public string $search = '';

    public function getConversationsProperty()
    {
        $user = auth()->user();

        $this->ensureConversations($user);

        return OwnerConversation::forUser($user)
            ->with('owner', 'tenant', 'contract.property')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function (OwnerConversation $conversation): object {
                $otherUser = $conversation->theOtherUser(auth()->user());
                $unread = $conversation->unreadCountFor(auth()->id());
                $lastMessage = $conversation->messages()->first();

                return (object) [
                    'id' => $conversation->id,
                    'other_user' => $otherUser,
                    'property' => $conversation->contract->property,
                    'last_message' => $lastMessage?->body,
                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count' => $unread,
                ];
            })
            ->filter(function ($item) {
                if (empty($this->search)) {
                    return true;
                }

                return str_contains(strtolower($item->other_user->name), strtolower($this->search))
                    || str_contains(strtolower($item->property->title ?? ''), strtolower($this->search));
            });
    }

    private function ensureConversations(User $user): void
    {
        $contracts = Contract::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('tenant_email', $user->email);
            })
            ->with('creator')
            ->get();

        foreach ($contracts as $contract) {
            $owner = $contract->created_by === $user->id
                ? $user
                : $contract->creator;
            $tenant = $contract->tenant_email === $user->email
                ? $user
                : User::where('email', $contract->tenant_email)->first();

            if (! $owner || ! $tenant || $owner->is($tenant)) {
                continue;
            }

            OwnerConversation::firstOrCreate(
                ['contract_id' => $contract->id],
                [
                    'owner_id' => $owner->id,
                    'tenant_id' => $tenant->id,
                    'last_message_at' => now(),
                ],
            );
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function render(): View
    {
        return view('livewire.messenger.conversation-list', [
            'conversations' => $this->conversations,
        ]);
    }
}
