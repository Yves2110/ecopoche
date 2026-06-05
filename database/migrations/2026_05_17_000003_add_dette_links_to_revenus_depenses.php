<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revenus', function (Blueprint $table) {
            $table->foreignId('dette_id')->nullable()->after('budget_id')->constrained('dettes')->nullOnDelete();
            $table->foreignId('remboursement_id')->nullable()->after('dette_id')->constrained('remboursements')->nullOnDelete();
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->foreignId('dette_id')->nullable()->after('budget_id')->constrained('dettes')->nullOnDelete();
            $table->foreignId('remboursement_id')->nullable()->after('dette_id')->constrained('remboursements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('revenus', function (Blueprint $table) {
            $table->dropForeign(['dette_id']);
            $table->dropForeign(['remboursement_id']);
            $table->dropColumn(['dette_id', 'remboursement_id']);
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->dropForeign(['dette_id']);
            $table->dropForeign(['remboursement_id']);
            $table->dropColumn(['dette_id', 'remboursement_id']);
        });
    }
};
