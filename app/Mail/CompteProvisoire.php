<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompteProvisoire extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $motDePasseProvisoire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'EcoPoche — Votre compte a été créé');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.compte-provisoire');
    }
}
