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
        Schema::create('centres', function (Blueprint $table) {
           $table->string('id_centre',50)->primary();
           $table->string('adresse_centre',200)->nullable();
           $table->integer('seuil_centre')->nullable();
           $table->enum('type_centre',['Aviation','Marine'])->nullable();
            $table->decimal('montant_disponible')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centres');
    }
};
