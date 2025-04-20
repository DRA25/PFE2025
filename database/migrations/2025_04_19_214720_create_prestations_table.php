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
        Schema::create('prestations', function (Blueprint $table) {
            $table->integer('id_pres')->primary();
            $table->string('desc_pres');
            $table->string('libelle_pres');
            $table->integer('prix_pres');
            $table->date('date_pres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};
