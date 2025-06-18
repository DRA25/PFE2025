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
        Schema::create('bon_achat_prestation', function (Blueprint $table) {
            $table->primary(['n_ba', 'id_prest']);
            $table->integer('n_ba');
            $table->integer('id_prest');
            $table->foreign('n_ba')->references('n_ba')->on('bon_achats')->onDelete('cascade');
            $table->foreign('id_prest')->references('id_prest')->on('prestations')->onDelete('cascade');
            $table->integer('qte_bapr');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
