<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RevenusController;
use App\Http\Controllers\DepensesController;
use App\Http\Controllers\EpargneController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertesController;
use App\Http\Controllers\RapportsController;
use App\Http\Controllers\ProfilController;

// Redirection racine
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// ---- Routes publiques (guests) ----
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');

    // Mot de passe oublié
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// ---- Routes protégées ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Épargne ----
    Route::prefix('epargne')->name('epargne.')->group(function () {
        Route::get('/',                               [EpargneController::class, 'index'])->name('index');
        Route::post('/mensuel/{budget}',              [EpargneController::class, 'updateMensuel'])->name('mensuel.update');
        Route::post('/objectifs',                     [EpargneController::class, 'storeObjectif'])->name('objectifs.store');
        Route::post('/objectifs/{objectif}/verser',   [EpargneController::class, 'verserObjectif'])->name('objectifs.verser');
        Route::delete('/objectifs/{objectif}',        [EpargneController::class, 'destroyObjectif'])->name('objectifs.destroy');
    });

    // ---- Dépenses ----
    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/',                          [DepensesController::class, 'index'])->name('index');
        Route::post('/store',                    [DepensesController::class, 'store'])->name('store');
        Route::delete('/{depense}',              [DepensesController::class, 'destroy'])->name('destroy');
        Route::post('/categories',               [DepensesController::class, 'storeCategorie'])->name('categories.store');
        Route::post('/categories/plafonds',      [DepensesController::class, 'updateCategories'])->name('categories.plafonds');
    });

    // ---- Alertes ----
    Route::prefix('alertes')->name('alertes.')->group(function () {
        Route::get('/',                        [AlertesController::class, 'index'])->name('index');
        Route::post('/{alerte}/lue',           [AlertesController::class, 'marquerLue'])->name('lue');
        Route::post('/tout-lire',              [AlertesController::class, 'toutMarquerLu'])->name('tout_lire');
        Route::delete('/{alerte}',             [AlertesController::class, 'supprimer'])->name('supprimer');
        Route::delete('/',                     [AlertesController::class, 'toutSupprimer'])->name('tout_supprimer');
        Route::get('/compteur',               [AlertesController::class, 'compteur'])->name('compteur');
        Route::post('/analyser',               [AlertesController::class, 'analyser'])->name('analyser');
    });

    // ---- Administration ----
    Route::prefix('admin')->name('admin.')->middleware('throttle:admin')->group(function () {
        Route::get('/',                          [AdminController::class, 'index'])->name('index');
        Route::post('/comptes',                  [AdminController::class, 'creerCompte'])->name('comptes.store');
        Route::post('/comptes/{user}/toggle',    [AdminController::class, 'toggleActif'])->name('comptes.toggle');
        Route::post('/comptes/{user}/impersonner', [AdminController::class, 'impersonner'])->name('comptes.impersonner');
        Route::post('/stop-impersonner',         [AdminController::class, 'stopImpersonner'])->name('stop_impersonner');
        Route::put('/comptes/{user}',                  [AdminController::class, 'editCompte'])->name('comptes.update');
        Route::post('/comptes/{user}/reset-password',  [AdminController::class, 'resetPassword'])->name('comptes.reset_password');
        Route::get('/comptes/{user}/logs',             [AdminController::class, 'logs'])->name('comptes.logs');
    });

    // ---- Profil / Paramètres ----
    Route::prefix('profil')->name('profil.')->middleware('throttle:profil')->group(function () {
        Route::get('/',                          [ProfilController::class, 'index'])->name('index');
        Route::put('/infos',                     [ProfilController::class, 'updateInfos'])->name('update.infos');
        Route::put('/password',                  [ProfilController::class, 'updateMotDePasse'])->name('update.password');
        Route::put('/preferences',               [ProfilController::class, 'updatePreferences'])->name('update.preferences');
        Route::post('/categories',               [ProfilController::class, 'storeCategorie'])->name('categories.store');
        Route::put('/categories/{categorie}',    [ProfilController::class, 'updateCategorie'])->name('categories.update');
        Route::delete('/categories/{categorie}', [ProfilController::class, 'destroyCategorie'])->name('categories.destroy');
    });

    // ---- Rapports ----
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/',                       [RapportsController::class, 'index'])->name('index');
        Route::get('/export/csv',             [RapportsController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf',             [RapportsController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/pdf-comparatif',   [RapportsController::class, 'exportPdfComparatif'])->name('export.pdf.comparatif');
        Route::post('/export/pdf-graphique',   [RapportsController::class, 'exportPdfAvecGraphique'])->name('export.pdf.graphique');
        Route::get('/export/csv-comparatif',   [RapportsController::class, 'exportCsvComparatif'])->name('export.csv.comparatif');
        Route::get('/bilan-annuel',            [RapportsController::class, 'bilanAnnuel'])->name('bilan-annuel');
        Route::get('/bilan-annuel/pdf',        [RapportsController::class, 'exportPdfBilanAnnuel'])->name('bilan-annuel.pdf');
        Route::post('/bilan-annuel/pdf',       [RapportsController::class, 'exportPdfBilanAnnuelGraphique'])->name('bilan-annuel.pdf.graphique');
    });

    // ---- Revenus ----
    Route::prefix('revenus')->name('revenus.')->group(function () {
        Route::get('/',                                [RevenusController::class, 'index'])->name('index');
        Route::post('/salaire/{budget}',              [RevenusController::class, 'updateSalaire'])->name('salaire.update');
        Route::post('/store',                         [RevenusController::class, 'storeRevenu'])->name('store');
        Route::post('/debloquer/{revenu}',            [RevenusController::class, 'debloquerReserve'])->name('debloquer');
        Route::delete('/{revenu}',                    [RevenusController::class, 'destroyRevenu'])->name('destroy');
    });
});
