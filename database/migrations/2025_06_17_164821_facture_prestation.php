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


        Schema::create('facture_prestation', function (Blueprint $table) {
            $table->primary(['n_facture', 'id_prest']);
            $table->integer('n_facture');
            $table->integer('id_prest');
            $table->integer('prix_prest');
            $table->foreign('n_facture')->references('n_facture')->on('factures')->onDelete('cascade');
            $table->foreign('id_prest')->references('id_prest')->on('prestations')->onDelete('cascade');
            $table->integer('qte_fpr');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture_prestation');
    }
};
