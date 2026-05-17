<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'test:mail {email=ismaelyveskabore@gmail.com}';
    protected $description = 'Envoie un mail de test';

    public function handle(): void
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('Test EcoPoche - ' . now()->format('Y-m-d H:i:s'), function($m) use ($email) {
                $m->to($email)->subject('Test EcoPoche ' . now()->format('H:i:s'));
            });
            $this->info("Mail envoyé à {$email}");
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
        }
    }
}
