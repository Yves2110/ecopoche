<?php

namespace App\Mail;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteSoldeRouge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public Budget $budget,
        public float  $solde,
        public float  $ratio,
        public float  $budgetTotal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'EcoPoche — Alerte : budget en zone critique',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte-solde-rouge',
        );
    }
}
