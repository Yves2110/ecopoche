<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('montant', 15, 2);
            $table->date('date');
            $table->string('note', 255)->nullable();
            $table->boolean('imprevue')->default(false);
            $table->timestamps();

            $table->index(['budget_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
