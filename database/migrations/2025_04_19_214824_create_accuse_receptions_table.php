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
            $table->integer('id_acc')->primary();
            $table->string('method_pai');
            $table->string('ordre');
            $table->string('libelle');
            $table->integer('montant');
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
