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
        Schema::create('commande_pieces', function (Blueprint $table) {

            $table->integer('n_bc');
            $table->integer('id_piece');

            $table->primary(['n_bc', 'id_piece']);

            $table->foreign('n_bc')->references('n_bc')->on('bon_de_commandes')->onDelete('cascade');
            $table->foreign('id_piece')->references('id_piece')->on('pieces')->onDelete('cascade');
            $table->integer('qte_commandep');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_pieces');
    }
};
