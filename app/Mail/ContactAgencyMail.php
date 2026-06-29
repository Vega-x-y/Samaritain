<?php

namespace App\Mail;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactAgencyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crée une nouvelle instance du mailable.
     */
    public function __construct(
        private readonly Property $annonce,
        private readonly string $senderName,
        private readonly string $senderEmail,
        private readonly ?string $senderPhone,
        private readonly string $messageBody,
    ) {}

    /**
     * Définit l'enveloppe du mail.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau message concernant l'annonce : {$this->annonce->title}",
            replyTo: [
                new Address($this->senderEmail, $this->senderName),
            ],
        );
    }

    /**
     * Définit le contenu du mail (vue Markdown).
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-agency',
            with: [
                'annonce'      => $this->annonce,
                'senderName'   => $this->senderName,
                'senderEmail'  => $this->senderEmail,
                'senderPhone'  => $this->senderPhone,
                'messageBody'  => $this->messageBody,
                'date'         => now()->format('d/m/Y à H:i'),
                'annonceUrl'   => route('property.show', $this->annonce),
                'reference'    => $this->annonce->title,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
