<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Récapitulatif hebdomadaire : chaque lundi à 8h00
Schedule::command('ecopoche:recap-hebdomadaire')
    ->weeklyOn(1, '08:00')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('ecopoche:recap-hebdomadaire a échoué'));

// Vérification des budgets critiques : tous les jours à 18h00
Schedule::command('ecopoche:verifier-budgets-critiques')
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('ecopoche:verifier-budgets-critiques a échoué'));
