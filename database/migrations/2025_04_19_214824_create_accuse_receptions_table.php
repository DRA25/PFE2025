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
        Schema::create('accuse_receptions', function (Blueprint $table) {
            $table->integer('n_acc')->primary();
            $table->integer('n_facture');
            $table->integer('n_ba');
            $table->foreign('n_facture')->references('n_facture')->on('factures')->onDelete('cascade');
            $table->foreign('n_ba')->references('n_ba')->on('bon_achats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accuse_receptions');
    }
};
