<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quota_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revenu_id')->constrained('revenus')->cascadeOnDelete();
            $table->decimal('montant_brut', 15, 2);
            $table->decimal('montant_quota', 15, 2);
            $table->decimal('montant_dispo', 15, 2);
            $table->decimal('taux', 5, 2)->default(30.00);
            $table->decimal('debloquer', 15, 2)->default(0);
            $table->text('justification_deblocage')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quota_logs');
    }
};
