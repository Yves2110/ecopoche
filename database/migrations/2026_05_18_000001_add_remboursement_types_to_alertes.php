<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE alertes MODIFY COLUMN type ENUM(
            'budget_sain',
            'attention',
            'critique',
            'plafond_80',
            'plafond_depasse',
            'epargne_deficit',
            'reajustement',
            'quota_applique',
            'echeance_proche',
            'echeance_depassee',
            'remboursement_partiel',
            'dette_soldee'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE alertes MODIFY COLUMN type ENUM(
            'budget_sain',
            'attention',
            'critique',
            'plafond_80',
            'plafond_depasse',
            'epargne_deficit',
            'reajustement',
            'quota_applique',
            'echeance_proche',
            'echeance_depassee'
        ) NOT NULL");
    }
};
