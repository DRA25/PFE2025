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
        Schema::create('demande_prests', function (Blueprint $table) {
            $table->integer('id_dpr')->primary();
            $table->date('date_dpr');
            $table->string('etat_pr');
            $table->integer('qte_demandepr');
            $table->integer('id_atelier');
            $table->foreign('id_atelier')->references('id_atelier')->on('ateliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_prests');
    }
};
