<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'budget_sain',
                'attention',
                'critique',
                'plafond_80',
                'plafond_depasse',
                'epargne_deficit',
                'reajustement',
                'quota_applique',
            ]);
            $table->enum('gravite', ['info', 'warning', 'danger'])->default('info');
            $table->string('message', 500);
            $table->json('meta')->nullable();
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lu_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
