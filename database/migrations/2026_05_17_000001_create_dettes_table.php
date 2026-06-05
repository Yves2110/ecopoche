<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['emprunt', 'pret']);
            $table->string('partie', 120);
            $table->decimal('montant_initial', 15, 2);
            $table->date('date_operation');
            $table->date('date_echeance')->nullable();
            $table->decimal('interet_pct', 5, 2)->nullable();
            $table->boolean('affecte_budget')->default(false);
            $table->enum('statut', ['actif', 'solde', 'en_retard'])->default('actif');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dettes');
    }
};
