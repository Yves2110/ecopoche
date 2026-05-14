<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['salaire', 'bonus', 'extra'])->default('extra');
            $table->decimal('montant_brut', 15, 2);
            $table->decimal('montant_quota', 15, 2)->default(0);
            $table->decimal('montant_dispo', 15, 2)->default(0);
            $table->date('date');
            $table->string('description', 255)->nullable();
            $table->boolean('quota_applique')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenus');
    }
};
