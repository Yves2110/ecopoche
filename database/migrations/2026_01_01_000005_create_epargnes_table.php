<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epargnes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->decimal('objectif', 15, 2)->default(0);
            $table->decimal('reel', 15, 2)->default(0);
            $table->decimal('deficit', 15, 2)->default(0);
            $table->text('analyse')->nullable();
            $table->timestamps();

            $table->unique('budget_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epargnes');
    }
};
