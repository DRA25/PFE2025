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
        Schema::create('quantite__stockes', function (Blueprint $table) {
            $table->primary(['id_magasin', 'id_piece']);
            $table->integer('id_magasin');
            $table->integer('id_piece');
            $table->integer('prix_piece');
            $table->foreign('id_magasin')->references('id_magasin')->on('magasins')->onDelete('cascade');
            $table->foreign('id_piece')->references('id_piece')->on('pieces')->onDelete('cascade');
            $table->integer('qte_stocke');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantite__stockes');
    }
};
