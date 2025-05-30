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
        Schema::create('bon_achats', function (Blueprint $table) {
            $table->integer('n_ba')->primary();

            $table->date('date_ba');
            $table->integer('id_fourn');
            $table->string('n_dra');
            $table->foreign('id_fourn')->references('id_fourn')->on('fournisseurs')->onDelete('cascade');
            $table->foreign('n_dra')->references('n_dra')->on('dras')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_achats');
    }
};
