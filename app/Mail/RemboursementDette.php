<?php

namespace App\Mail;

use App\Models\Dette;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RemboursementDette extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public Dette  $dette,
        public float  $montant,
        public bool   $estSolde,
    ) {}

    public function envelope(): Envelope
    {
        $libelle = $this->dette->type === 'emprunt' ? 'Emprunt' : 'Prêt';

        $sujet = $this->estSolde
            ? "EcoPoche — {$libelle} de {$this->dette->partie} entièrement soldé !"
            : "EcoPoche — Paiement enregistré : {$libelle} de {$this->dette->partie}";

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.remboursement-dette');
    }

    /** @return list<string> */
    public function conseils(): array
    {
        return \App\Services\AlerteConseilsService::pourType('remboursement_partiel', [
            'dette_id' => $this->dette->id,
            'restant'  => (float) $this->dette->montant_restant,
        ]);
    }
}
