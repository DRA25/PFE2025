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
        Schema::create('commande_prests', function (Blueprint $table) {

            $table->integer('n_bc');
            $table->integer('id_prest');

            $table->primary(['n_bc', 'id_prest']);

            $table->foreign('n_bc')->references('n_bc')->on('bon_de_commandes')->onDelete('cascade');
            $table->foreign('id_prest')->references('id_prest')->on('prestations')->onDelete('cascade');
            $table->integer('qte_commandepr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_prests');
    }
};
