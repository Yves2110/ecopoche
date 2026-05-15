<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // % du salaire fixe automatiquement mis en réserve chaque mois (0 = désactivé)
            $table->unsignedTinyInteger('epargne_salaire_pct')->default(0)->after('quota_taux');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('epargne_salaire_pct');
        });
    }
};
