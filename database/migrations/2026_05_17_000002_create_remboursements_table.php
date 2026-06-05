<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remboursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dette_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant', 15, 2);
            $table->date('date');
            $table->boolean('affecte_budget')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['dette_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remboursements');
    }
};
