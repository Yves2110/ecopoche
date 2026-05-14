<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ── Seuils d'alerte budgétaire (% du budget total)
            $table->unsignedTinyInteger('seuil_attention')->default(70)->after('notifs_email');
            $table->unsignedTinyInteger('seuil_critique')->default(90)->after('seuil_attention');

            // ── Seuil d'alerte plafond catégorie (% du plafond mensuel)
            $table->unsignedTinyInteger('seuil_plafond_cat')->default(80)->after('seuil_critique');

            // ── Objectif d'épargne mensuel en % du revenu total
            $table->unsignedTinyInteger('objectif_epargne_pct')->default(10)->after('seuil_plafond_cat');

            // ── Jour du mois pour le récapitulatif email (1-28)
            $table->unsignedTinyInteger('jour_bilan_email')->default(1)->after('objectif_epargne_pct');

            // ── Afficher les montants masqués (mode discret)
            $table->boolean('mode_discret')->default(false)->after('jour_bilan_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'seuil_attention', 'seuil_critique', 'seuil_plafond_cat',
                'objectif_epargne_pct', 'jour_bilan_email', 'mode_discret',
            ]);
        });
    }
};
