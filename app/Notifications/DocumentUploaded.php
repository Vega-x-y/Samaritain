<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploaded extends Notification
{
    use Queueable;

    public $document;

    public $chantier;

    /**
     * Create a new notification instance.
     */
    public function __construct($document, $chantier)
    {
        $this->document = $document;
        $this->chantier = $chantier;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $chantierNom = $this->chantier?->nom ?? 'un chantier';

        return (new MailMessage)
            ->subject('Nouveau document ajouté')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Un nouveau document a été ajouté au chantier : {$chantierNom}")
            ->line("Document : {$this->document->nom}")
            ->action('Voir le document', url('/artisan/finances'))
            ->line('Merci d\'utiliser notre plateforme.');
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
            'chantier_id' => $this->chantier?->id,
            'chantier_nom' => $this->chantier?->nom,
            'message' => "Nouveau document '{$this->document->nom}' ajouté au chantier '{$this->chantier?->nom}'",
        ];
    }
}
