<?php

namespace App\Mail;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecapHebdomadaire extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public Budget $budget,
        public float  $totalDepenses,
        public float  $budgetTotal,
        public float  $solde,
        public float  $ratio,
        public array  $topCategories,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'EcoPoche — Récapitulatif budgétaire de la semaine',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recap-hebdomadaire',
        );
    }
}
