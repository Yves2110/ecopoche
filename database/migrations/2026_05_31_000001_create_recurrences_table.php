<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['depense', 'revenu']);
            $table->foreignId('categorie_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('revenu_type', ['bonus', 'extra'])->nullable();
            $table->unsignedInteger('montant');
            $table->unsignedTinyInteger('jour_du_mois')->default(1);
            $table->string('libelle', 255)->nullable();
            $table->boolean('imprevue')->default(false);
            $table->boolean('is_active')->default(true);
            $table->char('last_generated_ym', 7)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrences');
    }
};
