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
        Schema::create('d_r_a_s', function (Blueprint $table) {

            $table->string('n_dra')->unique();
            $table->date('periode');
            $table->string('etat');
            $table->integer('cmp_gen');
            $table->integer('cmp_ana');
            $table->integer('debit');
            $table->string('libelle_dra');
            $table->date('date_dra');
            $table->string('fourn_dra');
            $table->string('destinataire_dra');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('d_r_a_s');
    }
};
