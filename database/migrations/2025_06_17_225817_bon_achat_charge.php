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
        Schema::create('bon_achat_charge', function (Blueprint $table) {
            $table->primary(['n_ba', 'id_charge']);
            $table->integer('n_ba');
            $table->integer('id_charge');
            $table->foreign('n_ba')->references('n_ba')->on('bon_achats')->onDelete('cascade');
            $table->foreign('id_charge')->references('id_charge')->on('charges')->onDelete('cascade');
            $table->integer('qte_bac');

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
