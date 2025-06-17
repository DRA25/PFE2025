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
        Schema::create('facture_charge', function (Blueprint $table) {
            $table->primary(['n_facture', 'id_charge']);
            $table->integer('n_facture');
            $table->integer('id_charge');
            $table->foreign('n_facture')->references('n_facture')->on('factures')->onDelete('cascade');
            $table->foreign('id_charge')->references('id_charge')->on('charges')->onDelete('cascade');
            $table->integer('qte_fc');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture_charge');
    }
};
