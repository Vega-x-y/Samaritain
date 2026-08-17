<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DevisSigned extends Notification
{
    use Queueable;

    public $document;

    public $client;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $client)
    {
        $this->document = $document;
        $this->client = $client;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Canal database uniquement pour éviter les erreurs SMTP/Resend en production et dev
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_nom' => $this->document->nom,
            'document_type' => $this->document->type,
            'client_id' => $this->client->id,
            'client_nom' => $this->client->nom,
            'signed_at' => $this->document->signed_at?->toIso8601String(),
            'message' => "Le client {$this->client->nom} a accepté le devis {$this->document->nom}",
        ];
    }
}
