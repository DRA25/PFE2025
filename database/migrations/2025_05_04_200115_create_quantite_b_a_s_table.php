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
        Schema::create('quantite_b_a_s', function (Blueprint $table) {
            $table->integer('n_ba');
            $table->integer('id_piece');
            $table->integer('prix_piece');
            $table->integer('qte_ba');


            $table->primary(['n_ba', 'id_piece']);

            $table->foreign('n_ba')->references('n_ba')->on('bon_achats')->onDelete('cascade');
            $table->foreign('id_piece')->references('id_piece')->on('pieces')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantite_b_a_s');
    }
};
