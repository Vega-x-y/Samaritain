<?php

namespace App\Notifications;

use App\Models\OwnerConversation;
use App\Models\OwnerMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public OwnerMessage $message,
        public OwnerConversation $conversation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;

        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'body' => $this->message->body,
            'type' => 'new_message',
        ];
    }
}
