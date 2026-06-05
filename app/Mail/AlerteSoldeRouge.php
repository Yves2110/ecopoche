<?php

namespace App\Mail;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteSoldeRouge extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public Budget $budget,
        public float  $solde,
        public float  $ratio,
        public float  $budgetTotal,
        public ?string $suggestions = null,
    ) {}

    /** @return list<string> */
    public function conseils(): array
    {
        return \App\Services\AlerteConseilsService::pourType(
            $this->solde < 0 ? 'critique' : 'attention',
            ['suggestions' => $this->suggestions],
            $this->budget
        );
    }

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
