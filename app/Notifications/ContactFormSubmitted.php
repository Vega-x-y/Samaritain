<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Contact $contact) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouveau message de contact : {$this->contact->subject}")
            ->greeting('Nouveau message de contact')
            ->line('Un visiteur a soumis un message via le formulaire de contact.')
            ->line('---')
            ->line("**De :** {$this->contact->name}")
            ->line("**Email :** {$this->contact->email}")
            ->line('**Téléphone :** '.($this->contact->phone ?: 'Non renseigné'))
            ->line('---')
            ->line("**Sujet :** {$this->contact->subject}")
            ->line('**Message :**')
            ->line($this->contact->message)
            ->line('---')
            ->line('**Informations supplémentaires :**')
            ->line('IP : '.$this->contact->ip_address)
            ->line('Date : '.$this->contact->created_at->format('d/m/Y à H:i'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
            'name' => $this->contact->name,
            'email' => $this->contact->email,
            'phone' => $this->contact->phone,
            'subject' => $this->contact->subject,
            'message' => $this->contact->message,
            'created_at' => $this->contact->created_at->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
