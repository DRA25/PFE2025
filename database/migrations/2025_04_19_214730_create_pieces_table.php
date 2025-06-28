<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces', function (Blueprint $table) {
            $table->integer('id_piece')->primary();
            $table->string('nom_piece');
            $table->float('tva', 5, 2); // TVA percentage (e.g., 19.6%)
            $table->string('marque_piece');
            $table->string('ref_piece');
            $table->string('id_centre');

            // Foreign keys for CompteGeneral and CompteAnalytique
            $table->string('compte_general_code');
            $table->string('compte_analytique_code');

            $table->foreign('id_centre')
                ->references('id_centre')
                ->on('centres')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('compte_general_code')
                ->references('code')
                ->on('comptes_generaux')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('compte_analytique_code')
                ->references('code')
                ->on('comptes_analytiques')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->dropForeign(['id_centre']);
            $table->dropForeign(['compte_general_code']);
            $table->dropForeign(['compte_analytique_code']);
        });

        Schema::dropIfExists('pieces');
    }
};
