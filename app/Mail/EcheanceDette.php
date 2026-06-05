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

class EcheanceDette extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public Dette  $dette,
        public string $situation,  // 'proche' ou 'depassee'
        public int    $jours,       // jours restants (positif) ou de retard (négatif)
    ) {}

    /** @return list<string> */
    public function conseils(): array
    {
        $type = match (true) {
            $this->situation === 'depassee' => 'echeance_depassee',
            $this->jours === 1 => 'echeance_j1',
            default => 'echeance_proche',
        };

        return \App\Services\AlerteConseilsService::pourType($type, [
            'dette_id' => $this->dette->id,
            'partie'   => $this->dette->partie,
            'restant'  => (float) $this->dette->montant_restant,
        ]);
    }

    public function envelope(): Envelope
    {
        $libelle = $this->dette->type === 'emprunt' ? 'Emprunt' : 'Prêt';
        $sujet = $this->situation === 'depassee'
            ? "EcoPoche — Échéance dépassée : {$libelle} de {$this->dette->partie}"
            : "EcoPoche — Échéance proche : {$libelle} de {$this->dette->partie}";

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.echeance-dette');
    }
}
