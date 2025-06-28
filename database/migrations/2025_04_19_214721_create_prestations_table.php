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
        Schema::create('prestations', function (Blueprint $table) {
            $table->integer('id_prest')->primary();
            $table->string('nom_prest');
            $table->string('desc_prest');
            $table->date('date_prest');
            $table->float('tva', 5, 2);
            $table->string('compte_general_code');
            $table->string('compte_analytique_code');


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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};
