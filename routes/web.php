<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RevenusController;
use App\Http\Controllers\DepensesController;

// Redirection racine
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// ---- Routes publiques (guests) ----
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ---- Routes protégées ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Dépenses ----
    Route::prefix('depenses')->name('depenses.')->group(function () {
        Route::get('/',                          [DepensesController::class, 'index'])->name('index');
        Route::post('/store',                    [DepensesController::class, 'store'])->name('store');
        Route::delete('/{depense}',              [DepensesController::class, 'destroy'])->name('destroy');
        Route::post('/categories',               [DepensesController::class, 'storeCategorie'])->name('categories.store');
        Route::post('/categories/plafonds',      [DepensesController::class, 'updateCategories'])->name('categories.plafonds');
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
