<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('mois');
            $table->unsignedSmallInteger('annee');
            $table->decimal('salaire_fixe', 15, 2)->default(0);
            $table->decimal('solde_charges', 15, 2)->default(0);
            $table->decimal('epargne_objectif', 15, 2)->default(0);
            $table->boolean('archive')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
