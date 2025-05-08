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
        Schema::create('commande_charges', function (Blueprint $table) {

            $table->integer('n_bc');
            $table->integer('id_charge');
            $table->primary(['n_bc', 'id_charge']);

            $table->foreign('n_bc')->references('n_bc')->on('bon_de_commandes')->onDelete('cascade');
            $table->foreign('id_charge')->references('id_charge')->on('charges')->onDelete('cascade');
            $table->integer('qte_commandec');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_charges');
    }
};
