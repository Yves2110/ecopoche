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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom', 100)->after('name')->default('');
            $table->string('prenom', 100)->after('nom')->default('');
            $table->boolean('onboarding_done')->after('mode_discret')->default(false);
        });

        // Migrer les données existantes : séparer name en prenom + nom
        \DB::table('users')->orderBy('id')->each(function ($u) {
            $parts  = explode(' ', trim($u->name), 2);
            $prenom = $parts[0] ?? '';
            $nom    = $parts[1] ?? '';
            \DB::table('users')->where('id', $u->id)->update([
                'prenom' => $prenom,
                'nom'    => $nom,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nom', 'prenom', 'onboarding_done']);
        });
    }
};
