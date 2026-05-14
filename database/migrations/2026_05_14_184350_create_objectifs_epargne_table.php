<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('objectifs_epargne', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->decimal('montant_cible', 15, 2);
            $table->decimal('montant_actuel', 15, 2)->default(0);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('couleur', 20)->default('#006c49');
            $table->string('icone', 50)->default('savings');
            $table->text('note')->nullable();
            $table->boolean('atteint')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objectifs_epargne');
    }
};
