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
        Schema::create('demande_pieces', function (Blueprint $table) {
            $table->integer('id_dp')->primary();
            $table->date('date_dp');
            $table->string('etat_dp');
            $table->integer('qte_demandep');
            $table->integer('id_magasin');
            $table->integer('id_atelier');
            $table->foreign('id_magasin')->references('id_magasin')->on('magasins')->onDelete('cascade');
            $table->foreign('id_atelier')->references('id_atelier')->on('ateliers')->onDelete('cascade');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_pieces');
    }
};
