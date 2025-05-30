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
        Schema::create('attestation_s_f_s', function (Blueprint $table) {
            $table->integer('n_asf')->primary();
            $table->integer('n_facture');
            $table->foreign('n_facture')->references('n_facture')->on('factures')->onDelete('cascade');
            $table->date('date_asf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_s_f_s');
    }
};
