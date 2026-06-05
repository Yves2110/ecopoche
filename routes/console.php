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

// Vérification des échéances emprunts/prêts : tous les jours à 9h00
Schedule::command('ecopoche:verifier-echeances-dettes')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('ecopoche:verifier-echeances-dettes a échoué'));

// Dépenses et revenus récurrents : chaque jour à 6h00
Schedule::command('ecopoche:generer-recurrences')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('ecopoche:generer-recurrences a échoué'));
