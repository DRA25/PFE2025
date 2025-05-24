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
        Schema::create('quantite__f_s', function (Blueprint $table) {
            $table->primary(['n_facture', 'id_piece']);
            $table->integer('n_facture');
            $table->integer('id_piece');
            $table->foreign('n_facture')->references('n_facture')->on('factures')->onDelete('cascade');
            $table->foreign('id_piece')->references('id_piece')->on('pieces')->onDelete('cascade');
            $table->integer('qte_f');
            $table->integer('montant_facture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantite__f_s');
    }
};
